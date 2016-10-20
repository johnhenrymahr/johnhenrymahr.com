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
    it('sets viewClass property', function () {
      view = new BaseView({viewClass: 'testing'})
      chai.expect(view.viewClass).to.equal('testing')
    })
    it('sets childViewContainer property if it is passed into constructor', function () {
      view = new BaseView({childViewContainer: '.testContainer'})
      chai.expect(view.childViewContainer).to.equal('.testContainer')
    })
    it('sets template if template function provided', function () {
      view = new BaseView({template: function () {}})
      chai.expect(view.template).to.be.a('function')
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
    it('returns view instance if no template defined', function () {
      view.cid = '343'
      chai.expect(view.render()).to.equal(view)
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
    it('triggers view:render event', function () {
      var options = {foo: 'bar'}
      var triggerStub = sandbox.stub(view, 'trigger')
      view._templateCallback(options, null, 'string')
      chai.expect(triggerStub.calledWith('view:render')).to.be.true
    })
    it('calls _attach with proper params', function () {
      var options = {foo: 'bar'}
      var html = '<div>stuff</div>'
      var stub = sandbox.stub(view, '_attach')
      view._templateCallback(options, null, html)
      chai.expect(stub.calledWith(html, options)).to.be.true
    })
  })
  context('_attach callback', function () {
    it('calls attach ', function () {
      var stub = sandbox.stub(view, 'attach')
      view._attach()
      chai.expect(stub.calledOnce).to.be.true
    })
    it('calls onAttach if defined', function () {
      var stub = view.onAttach = sandbox.stub()
      view._attach()
      chai.expect(stub.calledOnce).to.be.true
    })
    it('triggers view:attach event', function () {
      var stub = sandbox.stub(view, 'trigger')
      view._attach()
      chai.expect(stub.calledWith('view:attach')).to.be.true
      chai.expect(stub.calledOnce).to.be.true
    })
    it('calls render on child views if defined', function () {
      view._children = ['a', 'b', 'c']
      var stub = sandbox.stub(view, '_renderChildViews')
      view._attach()
      chai.expect(stub.calledOnce).to.be.true
    })
  })
  context('_renderChildViews method', function () {
    var children = [
      {
        id: 'a1',
        render: function () {
          return {
            el: 'a'
          }
        }
      },
      {
        id: 'b2',
        render: function () {
          return {
            el: 'b'
          }
        }
      },
      {
        id: 'c3',
        render: function () {
          return {
            el: 'c'
          }
        }
      }

    ]
    it('calls _getChildViewContainer', function () {
      var stub = sandbox.stub(view, '_getChildViewContainer')
      view._renderChildViews()
      chai.expect(stub.calledOnce).to.be.true
    })
    it('calls container append for each view', function () {
      view._children = children
      sandbox.stub(view, '_getChildViewContainer')
      var containerStub = {
        append: sandbox.stub()
      }
      view._getChildViewContainer.returns(containerStub)
      view._renderChildViews()
      chai.expect(containerStub.append.calledThrice).to.be.true
      chai.expect(containerStub.append.firstCall.calledWith('a')).to.be.true
      chai.expect(containerStub.append.secondCall.calledWith('b')).to.be.true
      chai.expect(containerStub.append.thirdCall.calledWith('c')).to.be.true
    })
    it('triggers view:attachChild for each child view and passes child view as arg', function () {
      view._children = children
      sandbox.stub(view, '_getChildViewContainer')
      var containerStub = {
        append: sandbox.stub()
      }
      view._getChildViewContainer.returns(containerStub)
      var stub = sandbox.stub(view, 'trigger')
      view._renderChildViews()
      chai.expect(stub.calledThrice).to.be.true
      chai.expect(stub.firstCall.args[0]).to.equal('view:attachChild')
      chai.expect(stub.firstCall.args[1].id).to.equal('a1')
      chai.expect(stub.secondCall.args[1].id).to.equal('b2')
      chai.expect(stub.thirdCall.args[1].id).to.equal('c3')
    })
  })
  context('_getChildViewContainer method', function () {
    it('returns entire el if not childViewContainer defined', function () {
      view.$el = $('<div />')
      chai.expect(view._getChildViewContainer()).to.equal(view.$el)
    })
    it('returns a child container if _getChildViewContainer', function () {
      view.$el = $('<div><div class="content">aa</div></div>')
      view.childViewContainer = '.content'
      chai.expect(view._getChildViewContainer()).to.be.instanceof($)
      chai.expect(view._getChildViewContainer().parent().html()).to.equal('<div class="content">aa</div>')
    })
    it('throws error if child view element cannot be found', function () {
      view.$el = $('<div><div></div><div class="content">aa</div></div>')
      view.childViewContainer = '.notthere'
      chai.expect(function () {
        view._getChildViewContainer()
      }).to.throw(Error, 'Child view container not found in context.')
    })
  })
  context('destroy method', function () {
    it('calls onBeforeDestroy if defined before running destroy', function () {
      var stub = sandbox.stub()
      var undelegateStub = sandbox.stub(view, 'undelegateEvents')
      view.onBeforeDestroy = stub
      view.destroy()
      chai.expect(stub.calledOnce).to.be.true
      chai.expect(stub.calledBefore(undelegateStub))
    })
    it('triggers view:beforeDestroy before running destroy', function () {
      var stub = sandbox.stub(view, 'trigger')
      var undelegateStub = sandbox.stub(view, 'undelegateEvents')
      view.destroy()
      chai.expect(stub.calledTwice).to.be.true
      chai.expect(stub.calledWith('view:beforeDestroy')).to.be.true
      chai.expect(stub.calledBefore(undelegateStub)).to.be.true
    })
    it('calls onDestory if defined after running destroy', function () {
      var stub = sandbox.stub()
      var undelegateStub = sandbox.stub(view, 'undelegateEvents')
      view.onDestroy = stub
      view.destroy()
      chai.expect(stub.calledOnce).to.be.true
      chai.expect(stub.calledAfter(undelegateStub))
    })
    it('triggers view:destroy after running destroy', function () {
      var stub = sandbox.stub(view, 'trigger')
      var undelegateStub = sandbox.stub(view, 'undelegateEvents')
      view.destroy()
      chai.expect(stub.calledTwice).to.be.true
      chai.expect(stub.calledWith('view:destroy'))
      chai.expect(stub.calledAfter(undelegateStub))
    })
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
