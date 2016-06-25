var $ = require('jquery')
var _ = require('lodash')
var View = require('app/views/_baseView')
var manifest = require('app/utils/_manifest').json
var App = require('app/app')
var buildElement = require('app/utils/buildContainer')
var views = require('app/views/_views')
require('app/less/main.less')

module.exports = View.extend(_.merge({
  _views: views,
  _manifest: manifest,
  _getSections: function (options) {
    var elements = []
    if (this._manifest.sections.length) {
      _.each(this._manifest.sections, _.bind(function (section) {
        if (_.has(section, 'id')) {
          var instance = this._getViewInstance(section)
          if (_.isObject(instance)) {
            elements.push(instance.render().el)
          }
        }
        if (_.isArray(section.children)) {
          var container = buildElement(section.container)
          _.each(section.children, _.bind(function (child) {
            var instance = this._getViewInstance(child)
            if (_.isObject(instance)) {
              this.trigger('child:instance', instance)
              container.append(instance.render(options).el)
              elements.push(container)
            }
          }, this))
        }
      }, this))
    }
    return elements
  },

  _getViewInstance: function (item) {
    var instance
    _.defaults(item || {}, {
      attributes: {},
      selector: 'div',
      template: false
    })
    if (!item.id) {
      return instance
    }
    var className = item.id + 'View'
    if (_.has(this._views, className) && _.isFunction(this._views[className])) {
      instance = new this._views[className](
        _.merge(item.attributes || {}, {
          el: $(item.selector, this.$el),
          model: App.model.getModel(item.id)
        }))
    }
    return instance
  },

  template: require('app/dust/' + manifest.template),

  onAttach: function (options) {
    _.each(this._getSections(options), _.bind(function (section) {
      this.$el.append(section)
    }, this))
  }
}, manifest.attributes))
