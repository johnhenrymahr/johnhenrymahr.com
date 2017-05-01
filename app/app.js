var $ = require('jquery')
var _ = require('lodash')
var Backbone = require('backbone')
var Router = require('router')

module.exports = {
  vent: _.clone(Backbone.Events),

  router: new Router(),

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
    return this.getVentPromise(event)
  },

  getVentPromise: function (vent) {
    if (_.has(this._ventPromises, vent) && _.isFunction(this._ventPromises[vent].promise)) {
      return this._ventPromises[vent].promise()
    }
  },

  all: function (promises) {
    promises = promises || []
    var $d = new $.Deferred()
    $.when.apply($, promises).done(function () {
      $d.resolve()
    })
    return $d.promise()
  },

  _track: function (eventCategory, eventLabel, eventAction, eventValue) {
    eventAction = eventAction || 'click'
    if (_.isUndefined(eventCategory) || _.isUndefined(eventLabel)) {
      return
    }
    var track = {
      hitType: 'event',
      eventCategory: eventCategory,
      eventAction: eventAction,
      eventLabel: eventLabel
    }
    if (eventValue) {
      track.eventValue = eventValue
    }
    if (_.isFunction(window, 'ga')) {
      window.ga('send', track)
    }
    if (window.localDev) {
      console.log('ga() tracking ', typeof track, track)
    }
  },

  eventTracking: function (e) {
    var $ele = $(e.currentTarget)
    var track = $ele.data('track')
    if (track) {
      this._track($ele.prop('tagName') + ':' + $ele.attr('class') || '', track)
    }
  },

  setupTracking: function () {
    _.bindAll(this, ['_track', 'eventTracking'])
    this.vent.off('app:track')
    this.vent.on('app:track', this._track)
    $(document)
      .off('click', 'a, button, input[type=submit]', this.eventTracking)
      .on('click', 'a, button, input[type=submit]', this.eventTracking)
  },

  setupMenuListener: function () {
    this.vent.on('app:navigate', this._menuListener, this)
  },

  ready: function () {
    this.setupTracking()
    this.vent.trigger('app:ready')
    return this
  },

  start: function () {
    this.router.listen()
    this.vent.once('core:animationEnd', function () {
      Backbone.history.start()
    })
    this.vent.trigger('app:start')
    return this
  }

}
