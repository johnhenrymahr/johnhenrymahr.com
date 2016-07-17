var $ = require('jquery')
var _ = require('lodash')
var MainView = require('./views/mainView')
var manifest = require('app/utils/_manifest').json
var AppModel = require('./models/appModel')
var App = require('./app')
require('dustjs-helpers')

App.model = new AppModel(jhmData, {parse: true})
App.view = new MainView(_.merge({
  el: $(manifest.selector)
}, manifest.attributes, {
  model: App.model
}))

App.onStart(function () {
  $('body').prepend(this.view.render().el)
})

if (localDev) {
  window.App = App
}

$(function () {
  App.start()
})
