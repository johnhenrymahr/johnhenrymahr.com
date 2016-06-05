var BaseView = require('app/views/_baseView')
var chai = require('chai')
var $ = require('jquery')
var sinon = require('sinon')

describe('baseView spec', function () {
  var view
  var sandbox
  beforeEach(function () {
    view = new BaseView()
    sandbox = sinon.sandbox.create()
  })

  afterEach(function () {
    view = null
    sandbox.restore()
  })

  context('instantiation ', function () {
    it('can be instantiated ', function () {
      chai.expect(view).to.be.a('object')
    })
    it('sets server rendered to false', function () {
      chai.expect(view._serverRendered).to.be.false
    })
    it('sets server rendered to true if dom populated ', function () {
      var $tpl = $('<main><h1>Just a test</h1></main>')
      var stub = sandbox.stub(BaseView.prototype, '_isServerRendered')
      stub.returns(true)
      view = new BaseView({
        el: $tpl
      })
      chai.expect(view._serverRendered).to.be.true
    })
  })
  context('data ', function () {
    it('getData merges template helpers', function () {
      view.templateHelpers = function () {
        return {test: 'var1'}
      }
      chai.expect(view._getData().test).to.equal('var1')
    })
    it('getData merges template helpers and serialzeData', function () {
      view.templateHelpers = function () {
        return {test: 'var1'}
      }
      view.serializeModel = function () {
        return {foo: 'bar'}
      }
      chai.expect(view._getData()).to.deep.equal({test: 'var1', foo: 'bar'})
    })
  })
})
