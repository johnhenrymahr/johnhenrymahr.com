var _ = require('lodash')
var Backbone = require('backbone')
module.exports = {
  vent: _.clone(Backbone.Events),

  onStart: function (handler, context) {
    context = context || this
    if (_.isFunction(handler)) {
      this.vent.once('app:start', _.bind(handler, context))
    }
  },

  start: function () {
    this.vent.trigger('app:start')
  }

}
