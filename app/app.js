var _ = require('lodash')
var Backbone = require('backbone')
var App = function () {
  this.vent = _.clone(Backbone.Events)

  this.onStart = function (handler, context) {
    context = context || this
    if (_.isFunction(handler)) {
      this.vent.once('app:start', _.bind(handler, context))
    }
  }

  this.start = function () {
    this.vent.trigger('app:start')
  }

  return this
}

module.exports = new App()
