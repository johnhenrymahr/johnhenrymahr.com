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
    it('generates a cid on instantiation', function () {
      chai.expect(view.cid).to.be.a('string')
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
  context('helpers ', function () {
    it(' lets serverRendered return value and sets internal to false', function () {
      view._serverRendered = true
      chai.expect(view.serverRendered()).to.be.true
      chai.expect(view._serverRendered).to.be.false
    })
  })
  context('data ', function () {
    it('getData merges template helpers', function () {
      view.templateHelpers = function () {
        return {test: 'var1'}
      }
      chai.expect(view._getData().test).to.equal('var1')
    })
    it('calls toJSON on model if a model is attached', function () {
      var stub = sandbox.stub()
      view.model = {
        toJSON: stub
      }
      view.serializeModel()
      chai.expect(stub.called).to.be.true
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
  context('render', function () {
    it('throws error if view has been previously destroyed ', function () {
      view._destroyed = true
      view.cid = '434'
      chai.expect(function () {
        view.render()
      }).to.throw(Error, 'The destroyed view: 434 cannot be rendered.')
    })
    it('throws error if no template is defined', function () {
      view.cid = '343'
      chai.expect(function () {
        view.render()
      }).to.throw(Error, 'The view: 343 has no template defined.')
    })
    it('calls template function if server render false', function () {
      var stub = sandbox.stub()
      view.template = stub
      view.render()
      chai.expect(stub.calledOnce).to.be.true
    })
    it('does not call render if server rendered is true', function () {
      var stub = sandbox.stub()
      view.template = stub
      view._serverRendered = true
      view.render()
      chai.expect(stub.called).to.be.false
    })
    it('passes correct options to template callback', function () {
      view.template = function (tpl, callback) {
        callback(null, 'string')
      }
      var callStub = sandbox.stub(view, '_templateCallback')
      var options = {foo: 'bar'}
      view.render(options)
      chai.expect(callStub.calledWith(options, null, 'string')).to.be.true
    })
  })
  context('on render callback', function () {
    it('calls onRender if defined', function () {
      var options = {foo: 'bar'}
      view.onRender = sandbox.stub()
      view._templateCallback(options, null, 'string')
      chai.expect(view.onRender.calledWith(options)).to.be.true
    })
    it('calls _attach with proper params', function () {
      var options = {foo: 'bar'}
      var html = '<div>stuff</div>'
      var stub = sandbox.stub(view, '_attach')
      view._templateCallback(options, null, html)
      chai.expect(stub.calledWith(html, options)).to.be.true
    })
  })
  context('destroy method', function () {
    it('calls undelegateEvents', function () {
      sandbox.stub(view, 'undelegateEvents')
      view.destroy()
      chai.expect(view.undelegateEvents.called).to.be.true
    })
    it('destroys element data', function () {
      sandbox.stub(view, 'undelegateEvents')
      sandbox.stub(view, 'remove')
      view.$el = $('<div />')
      view.$el.data('foo', 'bar')
      view.destroy()
      chai.expect($.hasData(view.$el)).to.be.false
    })
    it('sets _detroyed to true on destroy', function () {
      view._destroyed = false
      view.destroy()
      chai.expect(view._destroyed).to.be.true
    })
    it('calls remove ', function () {
      sandbox.stub(view, 'remove')
      view.destroy()
      chai.expect(view.remove.called).to.be.true
    })
  })
})
