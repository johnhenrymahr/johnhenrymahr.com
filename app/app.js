var _ = require('lodash')
var Backbone = require('backbone')
module.exports = {
  vent: _.clone(Backbone.Events),

  _stateVars: {},

  setState: function (key, v) {
    this._stateVars[key] = v
  },

  getState: function (key) {
    if (_.has(this._stateVars, key)) {
      return this._stateVars[key]
    }
  },

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

  _ventPromises: {},

  registerVentPromise: function (event) {
    if (_.has(this._ventPromises, event) && this._ventPromises[event].state() === 'pending') {
      return
    }
    this._ventPromises[event] = new $.Deferred()
    var args = Array.prototype.slice.call(arguments)
    this.vent.once(event, _.bind(function () {
      this._ventPromises[event].resolve.apply(this, args.slice(1))
    }, this))
  },

  getVentPromise: function (vent) {
    if (_.has(this._ventPromises, vent)) {
      return this._ventPromises[vent].promise()
    }
  },

  ready: function () {
    this.vent.trigger('app:ready')
  },

  start: function () {
    this.vent.trigger('app:start')
  }

}
