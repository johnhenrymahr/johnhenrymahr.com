var App = require('app/app')
module.exports = {
  initialize: function () {
    App.addScrollTracker('#details__role-heading', {
      eventCategory: 'details page',
      eventLabel: 'role UI developer',
      eventAction: 'scroll'
    })
    App.addScrollTracker('#details__client-stack', {
      eventCategory: 'details page',
      eventLabel: 'client application stack',
      eventAction: 'scroll'
    })
    App.addScrollTracker('#details__end', {
      eventCategory: 'details page',
      eventLabel: 'end of page',
      eventAction: 'scroll'
    })
  }
}