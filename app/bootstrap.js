var $ = require('jquery')
var MainView = require('./views/mainView')
var AppModel = require('./models/appModel')
var App = require('./app')

App.model = new AppModel(jhmData)
App.view = new MainView({
  el: $('div:first'),
  model: App.model
})

App.onStart(function () {
  this.view.render()
})

$(function () {
  App.start()
})
