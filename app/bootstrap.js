var $ = require('jquery')
var App = require('./app')
var MainView = require('./views/mainView')
var AppModel = require('./models/appModel')
// less
require('./less/main.less')
var app = new App()
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
