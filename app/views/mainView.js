var $ = require('jquery')
var _ = require('lodash')
var View = require('app/views/_baseView')
var manifest = require('app/utils/_manifest')
var App = require('app/app')

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
      var key = path.replace('Model.js', '').replace(/^(.*[\\\/])/, '').toLowerCase()
      model.setModel(key, models(path))
    })
  }
  // handle mixins
  deps.mixins = {}
  var mixinFiles = require.context('app/views', false, /ViewMixin.js$/)
  mixinFiles.keys().forEach(function (path) {
    var key = path.replace('ViewMixin.js', '').replace(/^(.*[\\\/])/, '').toLowerCase()
    deps.mixins[key] = mixinFiles(path)
  })
  return deps
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
        var instance = this._getViewInstance(section)
        if (_.isObject(instance)) {
          if (_.isArray(section.children)) {
            _.each(section.children, _.bind(function (child) {
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

  template: require('app/dust/' + manifest.json.template)

}, manifest.json.attributes))
