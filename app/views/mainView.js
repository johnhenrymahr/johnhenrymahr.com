var $ = require('jquery')
var _ = require('lodash')
var View = require('app/views/_baseView')
var manifest = require('app/utils/_manifest')
var App = require('app/app')
var buildElement = require('app/utils/buildContainer')

// set up less style sheets
var lessFiles = require.context('app/less', false, /^[^_]*.less$/) // files ending in .less, but not begining with _
lessFiles.keys().forEach(function (path) {
  lessFiles(path)
})

// handle mixins
var mixins = {}
var mixinFiles = require.context('app/views', false, /ViewMixin.js$/)
mixinFiles.keys().forEach(function (path) {
  var key = path.replace('ViewMixin.js', '').replace(/^(.*[\\\/])/, '').toLowerCase()
  mixins[key] = mixinFiles(path)
})

module.exports = View.extend(_.merge({
  _mixins: mixins,
  _children: [], // child views
  _manifest: manifest.json,
  _getSections: function (options) {
    var elements = []
    if (this._manifest.sections.length) {
      _.each(this._manifest.sections, _.bind(function (section) {
        var instance = this._getViewInstance(section)
        if (_.isObject(instance)) {
          this._children.push(instance)
          elements.push(instance.render(options).el)
        }
        if (_.isArray(section.children)) {
          var container = buildElement(section.container)
          _.each(section.children, _.bind(function (child) {
            var instance = this._getViewInstance(child)
            if (_.isObject(instance)) {
              this._children.push(instance)
              container.append(instance.render(options).el)
              elements.push(container)
            }
          }, this))
        }
      }, this))
    }
    return elements
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

    instance = new ViewClass(
      _.merge(item.attributes || {}, {
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

  getContainer: function () {
    return (manifest.has('content')) ? this.$(this._manifest.content) : this.$el
  },

  onAttach: function (options) {
    this.$container = this.getContainer()
    _.each(this._getSections(options), _.bind(function (section) {
      this.$container.append(section)
    }, this))
  },

  onBeforeDestroy: function () {
    _.each(this._children, function (childView) {
      childView.destroy()
    }, this)
  }

}, manifest.attributes))
