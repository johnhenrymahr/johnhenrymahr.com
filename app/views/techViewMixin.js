var App = require('app/app')
var $ = require('jquery')
require('jquery-touchswipe')
module.exports = {
  initialize: function () {
    App.addScrollTracker('#tech_learning', {
      eventCategory: 'tech page',
      eventLabel: 'Learning RxJS',
      eventAction: 'scroll'
    })
    App.addScrollTracker('#tech_investigating', {
      eventCategory: 'tech page',
      eventLabel: 'Investigating Vue.js',
      eventAction: 'scroll'
    })
    App.addScrollTracker('#tech_other-stuff', {
      eventCategory: 'tech page',
      eventLabel: 'Other tech stuff',
      eventAction: 'scroll'
    })
  },
  onAttach: function () {
    this.$('.modal').on('shown.bs.modal', function (e) {
      var $modal = $(e.target)
      $modal.swipe({
        swipe: function (event, direction, distance, duration, fingerCount, fingerData) {
          switch (direction) {
            case 'left':
            case 'right':
              $modal.modal('hide')
              break
          }
        },
        threshold: 50,
        fingers: 'all'
      })
    })
  }
}
