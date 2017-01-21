var $ = require('jquery')
var _ = require('lodash')
var App = require('app/app')

module.exports = {

  handleMenuClick: function (e) {
    e.preventDefault()
    var $ele = $($(e.currentTarget).attr('href'))
    App.getVentPromise('core:expanded')
    .done(_.bind(function () {
      this.scrollToElement($ele)
    }, this))
    if (!App.getState('core:expanded')) {
      App.vent.trigger('core:expand')
    }
  },

  scrollToElement: function ($ele) {
    if (!($ele instanceof $)) {
      return
    }
    $('html, body').animate({
      scrollTop: $ele.offset().top
    }, 1200, 'swing', function () {
      $ele.get(0).focus()
    })
  }
}
