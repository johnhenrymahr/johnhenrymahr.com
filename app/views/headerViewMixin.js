var _ = require('lodash')
var App = require('app/app')
var itemHandler = require('app/utils/menuItemHandler')
module.exports = _.extend({}, itemHandler, {
  events: {
    'click .nav__item': 'handleMenuClick'
  },
  onAppReady: function () {
    this.listenToOnce(App.vent, 'core:expanded', _.bind(function () {
      this.listenTo(App.vent, 'scroll:expand', _.bind(this._expand, this))
      this.listenTo(App.vent, 'scroll:collapse', _.bind(this._collapse, this))
    }, this))
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
