var $ = require('jquery')
var MainView = require('./views/mainView')
var AppModel = require('./models/appModel')
var App = require('./app')

App.model = new AppModel(jhmData)
App.view = new MainView({
  model: App.model
})

App.onStart(function () {
  $('body').prepend(this.view.render().el)
})

$(function () {
  App.start()
})
