var TitleView = require('app/views/titleView')
var chai = require('chai')
var sinon = require('sinon')

describe('title view ', function () {
  var view
  var sandbox
  beforeEach(function () {
    view = new TitleView()
    sandbox = sinon.sandbox.create()
  })
  afterEach(function () {
    view = null
    sandbox.restore()
  })
  it('can be instantiated ', function () {
    chai.expect(view).to.be.a('object')
  })
  it('can render its template ', function () {
    chai.expect(view.render().$el.is(':empty')).to.be.false
  })
})
