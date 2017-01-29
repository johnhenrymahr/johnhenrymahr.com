var $ = require('jquery')
var _ = require('lodash')
var App = require('app/app')
var scrollToElement = require('./scrollToElement')

module.exports = _.extend({}, scrollToElement, {

  handleMenuClick: function (e) {
    e.preventDefault()
    var $ele = $($(e.currentTarget).attr('href'))
    var focusTarget = $(e.currentTarget).data('focus')
    if (_.isString(focusTarget)) {
      focusTarget = $(focusTarget)
    }
    App.getVentPromise('core:expanded')
    .done(_.bind(function () {
      this.scrollToElement($ele, focusTarget)
    }, this))
    if (!App.getState('core:expanded')) {
      App.vent.trigger('scroll:expand')
    }
  }
})
