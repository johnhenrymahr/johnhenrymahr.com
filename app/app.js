var _ = require('lodash')
var Backbone = require('backbone')
module.exports = {
  vent: _.clone(Backbone.Events),

  onStart: function (handler, context) {
    context = context || this
    if (_.isFunction(handler)) {
      this.vent.once('app:start', _.bind(handler, context))
    }
    this._transition = this._detectTransitionEvent()
  },

  _transition: null,

  _detectTransitionEvent: function () {
    var t
    var el = document.createElement('fakeelement')
    var transitions = {
      'transition': 'transitionend',
      'OTransition': 'oTransitionEnd',
      'MozTransition': 'transitionend',
      'WebkitTransition': 'webkitTransitionEnd'
    }

    for (t in transitions) {
      if (!_.isUndefined(el.style[t])) {
        return transitions[t]
      }
    }
    return null
  },

  ready: function () {
    this.vent.trigger('app:ready')
  },

  start: function () {
    this.vent.trigger('app:start')
  }

}
