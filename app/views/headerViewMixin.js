var _ = require('lodash')
var App = require('app/app')
var itemHandler = require('app/utils/menuItemHandler')
module.exports = _.extend({}, itemHandler, {
  events: {
    'click .nav__item': 'handleMenuClick'
  },
  onAppReady: function () {
    this.listenToOnce(App.vent, 'core:expanded', _.bind(this._expand, this))
    $(window).on('scroll', _.bind(_.debounce(function (e) {
      if ($(document).scrollTop() > 0) {
        this._expand()
      }
      if ($(document).scrollTop() === 0) {
        this._collapse()
      }
    }, 150), this))
  },
  _expand: function () {
    if (App.getState('core:expanded')) {
      this.$el.addClass('active')
    }
  },
  _collapse: function () {
    this.$el.removeClass('active')
  }
})
