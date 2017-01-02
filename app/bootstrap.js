var $ = require('jquery')
var _ = require('lodash')
var MainView = require('./views/mainView')
var manifest = require('app/utils/_manifest').json
var AppModel = require('./models/_appModel')
var App = require('./app')
require('dustjs-helpers')
require('bootstrap/dist/js/bootstrap')

App.model = new AppModel(window.jhmData, {parse: true})
App.view = new MainView(_.merge(
  {
    el: $(manifest.selector),
    childViewContainer: manifest.childViewContainer || null
  },
  manifest.attributes,
  {
    model: App.model
  }
))

App.onStart(function () {
  $('body').prepend(this.view.render().el)
})

if (window.localDev) {
  window.App = App
}

$(function () {
  App.start()
})
