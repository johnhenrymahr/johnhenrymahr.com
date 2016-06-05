var $ = require('jquery')
var _ = require('lodash')
var View = require('app/views/_baseView')
var manifest = require('app/utils/_manifest').json
var App = require('app/app')
var buildElement = require('app/utils/buildContainer')
var views = require('app/views/_views')
require('app/less/main.less')

module.exports = View.extend(_.merge({
  _getSections: function (options) {
    var elements = []
    if (manifest.sections.length) {
      _.each(manifest.sections, _.bind(function (section) {
        if (_.isArray(section.children)) {
          var container = buildElement(section.container)
          _.each(section.children, _.bind(function (child) {
            var className = child.id + 'View'
            if (_.has(views, className)) {
              var instance = new views[child.id + 'View'](
                _.merge(child.attributes || {}, {
                  el: $(child.selector, this.$el),
                  model: App.model.getModel(child.id)
                }))
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

  template: require('app/dust/' + manifest.template),

  onAttach: function (options) {
    _.each(this._getSections(options), _.bind(function (section) {
      this.$el.append(section)
    }, this))
  }
}, manifest.attributes))
