var $ = require('jquery')
var Backbone = require('backbone')
var _ = require('lodash')
var View = require('./_view')
var manifest = require('./_manifest').json
var App = require('../app')
var viewClass = {
  titleView: require('./titleView')
}

function getViews () {
  var views = []
  if (manifest.children.length) {
    _.each(manifest.children, function (view) {
      views.push(new viewClass[view.id + 'View'](
        _.merge(view.attributes, {
          model: App.model.getModel()
        })))
    })
  }
  return views
}

module.exports = View.extend(_.merge({
  _views: getViews,
  template: require('app/dust/' + manifest.template),
  _getChildren: function (options) {
    var $children = $('<div class="content" />')
    _.each(_.result(this, '_views'), function (view) {
      if (view instanceof Backbone.View) {
        $children.append(view.render().el)
      }
    })
    return $children
  },
  onAttach: function (options) {
    this.$el.append(this._getChildren(options))
  }
}, manifest.attributes))
