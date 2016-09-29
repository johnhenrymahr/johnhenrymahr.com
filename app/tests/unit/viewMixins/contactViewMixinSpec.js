var view = require('app/views/contactViewMixin')
var chai = require('chai')
var sinon = require('sinon')
var $ = require('jQuery')

describe('contactView Mixin', function () {
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
    it('runs nValidationError method', function () {
      var stub = sandbox.stub(view, 'decorate')
      var el = $('<div />')
      view.$ = sandbox.stub()
      view.$.returns(el)
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
  context('decorator method', function () {
    var el
    beforeEach(function () {
      el = $('<div><input /></div>')
      view.decorate(el, 'test message')
    })
    it('popultes message text', function () {
      chai.expect(el.find('span.validation-error').text()).to.equal('test message')
    })
    it('puts span in correct location in DOM', function () {
      chai.expect(el.find('input').next()[0].tagName).to.equal('SPAN')
    })
    it('puts span with correct class in correct location in DOM', function () {
      chai.expect(el.find('input').next().attr('class')).to.equal('validation-error')
    })
    it('adds class to container', function () {
      chai.expect(el.hasClass('has-error')).to.be.true
    })
  })
  context('undecorator method', function () {
    var el
    beforeEach(function () {
      el = $('<div class="has-error"><input /><span class="validation-error"></span></div>')
      view.undecorate(el)
    })
    it('removes error class', function () {
      chai.expect(el.hasClass('has-error')).to.be.false
    })
    it('removes error span', function () {
      chai.expect(el.find('span.validation-error').length).to.equal(0)
    })
  })
})
