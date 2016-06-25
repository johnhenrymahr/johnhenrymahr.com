var SplashView = require('app/views/titleView')
var chai = require('chai')
var sinon = require('sinon')
describe('splash view ', function () {
  var view
  var sandbox
  beforeEach(function () {
    view = new SplashView()
    sandbox = sinon.sandbox.create()
  })
  afterEach(function () {
    view = null
    sandbox.restore()
  })
  it('can be instantiated ', function () {
    chai.expect(view).to.be.a('object')
  })
  it('can render a template', function () {
    chai.expect(view.render().$el.is(':empty')).to.be.false
  })
})
