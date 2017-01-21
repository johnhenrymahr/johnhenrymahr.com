var view = require('app/views/cvViewMixin')
var chai = require('chai')
var sinon = require('sinon')
var $ = require('jQuery')

describe('cvView Mixin spec', function () {
  var sandbox
  beforeEach(function () {
    sandbox = sinon.sandbox.create()
  })
  afterEach(function () {
    sandbox.restore()
  })
  context('initialize', function () {
    it('adds model listener if there is a model', function () {
      view.listenTo = sandbox.stub()
      var model = {}
      view.initialize({model: model})
      chai.expect(view.listenTo.calledWith(model)).to.be.true
    })
  })
  context('event handlers ', function () {
    it('onRequestClick calls togglePopover method', function () {
      var stub = sandbox.stub(view, 'togglePopover')
      var e = {preventDefault: sandbox.stub()}
      view.onRequestClick(e)
      chai.expect(stub.called).to.be.true
      chai.expect(e.preventDefault.called).to.be.true
    })
    it('runs onValidationError method', function () {
      var stub = sandbox.stub(view, 'decorate')
      var el = $('<div />')
      view.$ = sandbox.stub()
      view.$.returns({
        parents: function () {
          return el
        }
      })
      view.onValidationError({}, {name: 'a name error', email: 'a email error'}, {})
      chai.expect(stub.calledWith(el, 'a name error')).to.be.true
      chai.expect(stub.calledWith(el, 'a email error')).to.be.true
    })
    it('runs onInputFocus method', function () {
      var el = $('<div />')
      var stub = sandbox.stub(view, 'undecorate')
      view.$ = sandbox.stub()
      view.$.returns(el)
      view.onInputFocus({currentTarget: ''})
      chai.expect(stub.calledOnce).to.be.true
    })
    it('runs onInputBlur method', function () {
      var el = {
        attr: function () {
          return 'foo'
        },
        val: function () {
          return 'bar'
        }
      }
      view.model = {
        set: sandbox.stub()
      }
      view.$ = sandbox.stub()
      view.$.returns(el)
      view.onInputBlur({currentTarget: ''})
      chai.expect(view.model.set.calledWith('foo', 'bar', {validate: true})).to.be.true
    })
  })
})
