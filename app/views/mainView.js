var Backbone = require('backbone')
var _ = require('lodash')
var View = require('./_view')
var app = require('../app')
var template = require('../dust/mainTpl.dust')
var views = []
module.exports = View.extend({
  _views: views,
  template: template,
  onRender: function () {
    _.each(this._views, function (view) {
      if (view instanceof Backbone.View) {
        this.$el.append(view.render().el)
      }
    })
  }
})
