var Backbone = require('backbone')
var _ = require('lodash')
var $ = require('jquery')

module.exports = Backbone.Router.extend({

  routes: {
    'core': 'core',
    'details': 'details',
    'tech': 'tech',
    'cv': 'cv',
    'contact': 'contact'
  },

  currentRoute: '',

  listen: function () {
    this.on('route', _.bind(function (route) {
      this.currentRoute = route
      var $scrollTarget = $('#' + route)
      $('.onLoad').addClass('hidden fadeOut')
      $scrollTarget.removeClass('hidden')
      $('html, body').animate({
        scrollTop: $scrollTarget.offset().top
      }, 750, 'swing', function () {
        if ($scrollTarget.hasClass('fadeOut')) {
          $scrollTarget.removeClass('fadeOut')
        }
        if ($scrollTarget.find('.arrow').length) {
          $scrollTarget.find('.arrow').addClass('bounce')
        }
      })
    }, this))
  }

})
