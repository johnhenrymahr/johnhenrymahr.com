var $ = require('jquery')
var App = require('./app')
var MainView = require('./views/mainView')
var AppModel = require('./models/appModel')
//less
require('./less/main.less')

var app = new App()
app.model = new AppModel()
app.view  = new MainView({
  el: '#app',
  model: window.appModel
})

app.onStart(function() {
  this.view.render()
  console.log('app start  running')
})

$(function() {
  console.log('dom ready ')
    console.log(app.view)
  app.start()
})
