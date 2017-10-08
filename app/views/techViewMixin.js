var App = require('app/app')
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
  }
}