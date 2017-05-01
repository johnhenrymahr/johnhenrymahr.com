var _ = require('lodash')
var Backbone = require('backbone')
var App = require('app/app')
var MainView = require('app/views/mainView')
var chai = require('chai')
var sinon = require('sinon')
var views = {
  testView: Backbone.View.extend({})
}
var manifest = {
  template: 'mainTpl.dust',
  selector: 'main.mainView',
  attributes: {
    className: 'mainClass',
    tagName: 'main'
  },
  sections: [ {
    id: 'testContainer',
    template: 'splashTpl.dust',
    container: {
      attributes: {
        className: 'content'
      }
    },
    children: [
      {
        id: 'contact',
        selector: 'section.title',
        template: 'contactTpl.dust',
        attributes: {
          className: 'title container-fluid',
          tagName: 'section'
        }
      }
    ]
  }]
}
App.model = {
  getModel: function () {}
}

describe('MainView spec', function () {
  var view
  var sandbox
  beforeEach(function () {
    sandbox = sinon.sandbox.create()
    MainView.prototype.initialize = function () {} // prevent initialize from running
    view = new MainView()
    view._manifest = _.clone(manifest)
    view._mixins = {}
    view._children = []
    view._views = views
  })
  afterEach(function () {
    view = null
    sandbox.restore()
  })
  context('instantiation', function () {
    it('can be instantiated', function () {
      chai.expect(view).to.be.a('object')
    })
  })

  context('render', function () {
    it('can render its base template', function () {
      chai.expect(view.render().$el.is(':empty')).to.be.false
    })
    it('populates _mixins array on instantiation', function () {
      chai.expect(view._mixins).to.be.a('Object')
    })
  })

  context('getSections method', function () {
    it('returns  array', function () {
      var secs = view._getSections()
      chai.expect(secs).to.be.a('array')
    })
    it('pushes section instances onto stack', function () {
      view._getSections()
      chai.expect(view._children.length).to.equal(1)
    })
    it('calls getViewInstance for each iterateable section', function () {
      var spy = sandbox.spy(view, '_getViewInstance')
      view._getSections()
      chai.expect(spy.calledTwice).to.be.true
    })
    it('does not push invalid instances onto stack', function () {
      delete view._manifest.sections[0].children[0].id
      var secs = view._getSections()
      chai.expect(secs.length).to.equal(1)
      chai.expect(view._children.length).to.equal(1)
    })
  })

  context('extract atts method', function () {
    it('extracts attributes when they exist', function () {
      var test = {
        'tagName': 'section',
        'className': 'hoe',
        'tabindex': '0'
      }
      var expected = {
        tagName: 'section',
        className: 'hoe',
        attributes: {
          tabindex: '0'
        }
      }
      chai.expect(view._extractAttributes(test)).to.deep.equal(expected)
    })
    it('does not add attributes if there are not any', function () {
      var test = {
        'tagName': 'section',
        'className': 'hoe'
      }
      chai.expect(view._extractAttributes(test)).to.deep.equal(test)
    })
  })

  context('_setupEventProxy, _eventProxyHandler methods', function () {
    it('matches on event  name', function () {
      var test = /^(view:)/
      var eventName = 'view:testStuff'
      chai.expect(test.test(eventName)).to.be.true
    })
    it('creates new event name ', function () {
      var instance = {
        viewClass: 'tester'
      }
      var eventName = 'view:testStuff'
      chai.expect(eventName.replace(/^(view)/, instance.viewClass)).to.equal('tester:testStuff')
    })
    it('triggers correct event', function () {
      var stub = sandbox.stub(view, 'trigger')
      var child = new Backbone.View()
      child.viewClass = 'tester'
      view._eventProxyHandler(child, 'view:stuff', {foo: 'bar'})
      chai.expect(stub.calledWith('tester:stuff', {foo: 'bar'})).to.be.true
    })
  })

  context('_getViewInstance', function () {
    it('returns undefined if no id provided', function () {
      chai.expect(view._getViewInstance({})).to.be.undefined
    })
    it('returns view instance if requried atts provided', function () {
      chai.expect(view._getViewInstance({id: 'test'})).to.be.instanceof(Backbone.View)
    })
    it('sets up event proxy if proxy is true', function () {
      var stub = sandbox.stub(view, '_setupEventProxy')
      view._getViewInstance({id: 'test', proxy: true})
      chai.expect(stub.called).to.be.true
    })
    it('does not set up event proxy if proxy is false', function () {
      var stub = sandbox.stub(view, '_setupEventProxy')
      view._getViewInstance({id: 'test'})
      chai.expect(stub.called).to.be.false
    })
    it('extends base view with mixin if mixin is defined', function () {
      view._mixins = {
        test: {
          foo: 'bar'
        }
      }
      var instance = view._getViewInstance({id: 'test'})
      chai.expect(instance.foo).to.equal('bar')
    })
    it('calls mixin initialize if provided', function () {
      view._mixins = {
        test: {
          foo: 'bar',
          initialize: function () {
            this.foo = 'baz'
          }
        }
      }
      var instance = view._getViewInstance({id: 'test'})
      chai.expect(instance.foo).to.equal('baz')
    })
  })
  context('onPostRender method', function () {
    it('triggers app event', function () {
      var stub = sandbox.stub(App.vent, 'trigger')
      view.onPostRender()
      chai.expect(stub.calledWith('mainView:postRender')).to.be.true
    })
    it('calls add class on onLoad elements', function () {
      var b = {
        addClass: sandbox.stub()
      }
      var a = sandbox.stub(view, '$')
      a.returns(b)
      view.onPostRender()
      chai.expect(b.addClass.calledWith('hidden fadeOut')).to.be.true
      chai.expect(a.calledWith('.onLoad')).to.be.true
    })
  })
})
