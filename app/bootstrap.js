var $ = require('jquery')
var MainView = require('./views/mainView')
var AppModel = require('./models/appModel')
var app = require('./app')

app.model = new AppModel(jhmData)
app.view = new MainView({
  el: '#app',
  model: app.model
})

app.onStart(function () {
  this.view.render()
})

$(function () {
  app.start()
})
