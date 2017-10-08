var $ = require('jquery')
var _ = require('lodash')
var Backbone = require('backbone')
var Router = require('router')
var Cookie = require('js-cookie')

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

    if (_.isFunction(window, 'ga')) {
      window.ga(function (tracker) {
        var clientId = tracker.get('clientId')
        if (clientId) {
          Cookie.set('_jhm-cid', clientId)
        }
      })
    }
  },

  setupMenuListener: function () {
    this.vent.on('app:navigate', this._menuListener, this)
  },

  scrollHandler: function () {
    var windowOffset = $(window).scrollTop() + $(window).height()
    this._scrollTrackers = _.filter(_.map(this._scrollTrackers, _.bind(function (tracker) {
      if (tracker.$ele.is(':hidden')) {
        return tracker
      }
      if (!tracker.offset) {
        tracker.offset = Math.round(tracker.$ele.offset().top + tracker.$ele.outerHeight(true)) + 50
      }
      if (windowOffset > tracker.offset) {
        this._track(tracker.eventCategory, tracker.eventLabel, tracker.eventAction, tracker.eventValue)
      } else {
        return tracker
      }
    }, this)))
    if (!this._scrollTrackers.length) {
      $(window).unbind('scroll', this.scrollHandler)
    }
  },

  _scrollTrackers: [],

  /**
   * addScrollTracker
   *
   *   add a  tracker that will trigger a track event the first time [only] a tracked
   *   selector enters the view port
   * @param {string} selector jQuery selector of element to track (after render)
   * @param {object} tracker  tracker object,
   */
  addScrollTracker: function (selector, tracker) {
    tracker = tracker || {}
    if (_.isString(selector) && tracker.eventCategory && tracker.eventLabel) {
      this._scrollTrackers.push(_.merge(
        {
          selector: selector
        },
      tracker
      ))
    }
  },

  _setupScrollTrackers: function () {
    this._scrollTrackers = _.filter(_.map(this._scrollTrackers, function (tracker) {
      var $ele = $(tracker.selector)
      if ($ele.length) {
        tracker.$ele = $ele
        return tracker
      }
    }))
  },

  ready: function () {
    this.setupTracking()
    this._setupScrollTrackers()
    if (this._scrollTrackers.length) {
      this.scrollHandler = _.debounce(_.bind(this.scrollHandler, this), 100)
      $(window).scroll(this.scrollHandler)
    }
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
