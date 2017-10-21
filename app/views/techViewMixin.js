var App = require('app/app')
var $ = require('jquery')
var _ = require('lodash')
require('jquery-touchswipe')
module.exports = {
  clientRendered: false,
  initialize: function () {
    this.listenToOnce(App.router, 'route:tech', _.bind(function () {
      if (!this.clientRendered) {
        this.render()
      }
    }, this))
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
  onRender: function () {
    this.clientRendered = true
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
