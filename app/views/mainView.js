var $ = require('jquery')
var _ = require('lodash')
var View = require('app/views/_baseView')
var manifest = require('app/utils/_manifest')
var App = require('app/app')
require('app/plugins/jquery.disableScroll')

function setUpDependencies (model) {
  var deps = {}
  // set up less style sheets
  deps.lessFiles = require.context('app/less', false, /^[^_]*.less$/) // files ending in .less, but not begining with _
  deps.lessFiles.keys().forEach(function (path) {
    deps.lessFiles(path)
  })

  // models
  if (_.isFunction(model.setModel)) {
    var models = require.context('app/models', false, /^[^_]*Model.js$/)
    models.keys().forEach(function (path) {
      var key = path.replace('Model.js', '').replace(/^(.*[\\/])/, '').toLowerCase()
      model.setModel(key, models(path))
    })
  }
  // handle mixins
  deps.mixins = {}
  var mixinFiles = require.context('app/views', false, /ViewMixin.js$/)
  mixinFiles.keys().forEach(function (path) {
    var key = path.replace('ViewMixin.js', '').replace(/^(.*[\\/])/, '').toLowerCase()
    deps.mixins[key] = mixinFiles(path)
  })
  return deps
}

function disabled (obj) {
  return Boolean(_.isObject(obj) && _.has(obj, 'disabled') && obj.disabled === true)
}

module.exports = View.extend(_.merge({
  _mixins: null,
  _children: [], // child views (sections)
  _manifest: manifest.json,
  initialize: function (options) {
    var deps = setUpDependencies(options.model || {})
    this._mixins = deps.mixins
    this._getSections()
  },
  _getSections: function () {
    if (this._manifest.sections.length) {
      _.each(this._manifest.sections, _.bind(function (section) {
        if (disabled(section)) {
          return
        }
        var instance = this._getViewInstance(section)
        if (_.isObject(instance)) {
          if (_.isArray(section.children)) {
            _.each(section.children, _.bind(function (child) {
              if (disabled(child)) {
                return
              }
              instance._children.push(this._getViewInstance(child))
            }, this))
          }
          this._children.push(instance)
        }
      }, this))
    }
    return this._children
  },

  _setupEventProxy: function (instance) {
    this.listenTo(instance, 'all', _.wrap(instance, _.bind(this._eventProxyHandler, this)))
  },

  _eventProxyHandler: function () {
    var args = Array.prototype.slice.call(arguments)
    var instance = args.shift()
    var eventName = args.shift()
    if (/^(view:)/.test(eventName)) {
      var triggerName = eventName.replace(/^(view)/, instance.viewClass)
      args.unshift(triggerName)
      this.trigger.apply(this, args)
    }
  },

  _scrollDisabled: false,

  disableScroll: function () {
    if (!this._scrollDisabled) {
      this._scrollDisabled = true
      $(window).disablescroll({
        handleScrollbar: false
      })
    }
  },

  enableScroll: function () {
    if (this._scrollDisabled) {
      this._scrollDisabled = false
      $(window).disablescroll('undo')
    }
  },

  _extractAttributes: function (atts) {
    var nonAtts = ['className', 'id', 'tagName']
    var newAtts = _.pick(atts, nonAtts)
    var attAtts = _.omit(atts, nonAtts)
    if (!_.isEmpty(attAtts)) {
      newAtts.attributes = attAtts
    }
    return newAtts
  },

  _getViewInstance: function (item) {
    var instance
    _.defaults(item || {}, {
      attributes: {},
      selector: 'div',
      template: false,
      proxy: false
    })
    if (!item.id) {
      return instance
    }
    var template = (item.template) ? require('app/dust/' + item.template) : false
    var mixin = (_.isObject(this._mixins[item.id])) ? this._mixins[item.id] : {}
    var ViewClass = (!_.isEmpty(mixin)) ? View.extend(mixin) : View
    var atts = this._extractAttributes(item.attributes || {})
    instance = new ViewClass(
      _.merge(atts, {
        template: template,
        viewClass: item.id,
        el: $(item.selector, this.$el),
        model: App.model.getModel(item.id)
      }))

    if (item.proxy) {
      this._setupEventProxy(instance)
    }
    return instance
  },

  template: require('app/dust/' + manifest.json.template),

  onPostRender: function () {
    App.vent.trigger('mainView:postRender')
    var $onLoad = this.$('.onLoad')
    $onLoad.addClass('hidden fadeOut fadeUp')
    this.listenToOnce(App.vent, 'app:ready', _.bind(function () {
      this.bindScrollHandler()
    }, this))
    this.listenToOnce(App.vent, 'scroll:expand', _.bind(function () {
      $onLoad.removeClass('hidden')
      _.defer(function () {
        $onLoad.removeClass('fadeOut fadeUp')
      })
    }, this))
    this.listenTo(App.vent, 'scroll:disable', _.bind(function () {
      this.unbindScrollHandler()
      this.disableScroll()
    }, this))
    this.listenTo(App.vent, 'scroll:enable', _.bind(function () {
      this.bindScrollHandler()
      this.enableScroll()
    }, this))
  },

  unbindScrollHandler: function () {
    $(window).off('scroll')
  },

  bindScrollHandler: function () {
    $(window).on('scroll', _.bind(_.debounce(function (e) {
      if ($(document).scrollTop() > 0) {
        App.vent.trigger('scroll:expand')
      }
      if ($(document).scrollTop() === 0) {
        App.vent.trigger('scroll:collapse')
      }
    }, 250, {leading: true}), this))
  }

}, manifest.json.attributes))
