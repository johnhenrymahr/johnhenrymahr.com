var $ = require('jquery')
var _ = require('lodash')
var MainView = require('./views/mainView')
var manifest = require('app/utils/_manifest').json
var AppModel = require('./models/_appModel')
var app = require('./app')
require('dustjs-helpers')
require('bootstrap/dist/js/bootstrap')

app.model = new AppModel(window.jhmData, {parse: true})
app.view = new MainView(_.merge(
  {
    el: $(manifest.selector),
    childViewContainer: manifest.childViewContainer || null
  },
  manifest.attributes,
  {
    model: app.model
  }
))

app.onStart(function () {
  $('body').prepend(this.view.render().el)
})

if (window.localDev) {
  window.app = app
}

function loader () {
  var d = $.Deferred()
    // synchronous code
  _.delay(function () {
    d.resolve()
  }, 1000)
  return d
}

var mainRenderPromise = app.registerVentPromise('mainView:postRender')

$(function () {
  app.start().all([loader(), mainRenderPromise]).done(_.bind(app.ready, app))
})
