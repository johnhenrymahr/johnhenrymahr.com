var $ = require('jquery')
var _ = require('lodash')
var MainView = require('./views/mainView')
var manifest = require('app/utils/_manifest').json
var AppModel = require('./models/_appModel')
var app = require('./app')
var WebFont = require('webfontloader')

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

// fonts
var fontPromise = new $.Deferred()
WebFont.load({
  custom: {
    families: ['struktur_proregular', 'struktur_proitalic', 'struktur_proheavy', 'struktur_probold_italic', 'struktur_probold', 'didonesquebold']
  },
  active: function () { // fonts loaded OK
    fontPromise.resolve()
  },
  inactive: function () { // fonts could not be loaded
    fontPromise.resolve()
  }
})

function loader () {
  var d = $.Deferred()
  $.when(fontPromise).done(function () {
    _.delay(function () { // get the browser to respect scollTop
      window.scrollTo(0, 0)
      d.resolve()
    }, 100)
  })
  return d
}

var mainRenderPromise = app.registerVentPromise('mainView:postRender')

$(window).load(function () {
  app.start().all([loader(), mainRenderPromise]).done(_.bind(app.ready, app))
})
