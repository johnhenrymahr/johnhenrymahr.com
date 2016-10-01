var chai = require('chai')
var AppModel = require('app/models/_appModel')
var Backbone = require('backbone')
var sinon = require('sinon')

describe('App model spec ', function () {
  var sandbox
  var model
  beforeEach(function () {
    sandbox = sinon.sandbox.create()
    model = new AppModel()
  })
  afterEach(function () {
    sandbox.restore()
    model = null
  })
  context('instantiation ', function () {
    it('can be instantiated', function () {
      chai.expect(model).to.be.a('object')
      chai.expect(model).to.be.instanceOf(Backbone.Model)
    })
  })
  context('getModel', function () {
    it('getModel returns a model when property is a model', function () {
      model._children['tester'] = new Backbone.Model()
      chai.expect(model.getModel('tester')).to.be.instanceOf(Backbone.Model)
    })
    it('getModel returns null when no model is found', function () {
      chai.expect(model.getModel('sally')).to.be.null
    })
  })
  context('setModel', function () {
    it('sets a basic model', function () {
      var Clone = Backbone.Model.extend({})
      model.setModel('test', Clone)
      chai.expect(model._children['test']).to.be.a('object')
      chai.expect(model._children['test']).to.be.instanceof(Clone)
    })
    it('sets model with bootstrap data', function () {
      var model = new AppModel({
        _moduleData: {
          test4: {
            foo: 'bar'
          }
        }
      }, {parse: true})
      model.setModel('test4', Backbone.Model)
      chai.expect(model._children.test4).to.be.a('object')
      chai.expect(model._children.test4.get('foo')).to.equal('bar')
    })
  })
  it('sets model with bootstrap options', function () {
    var model = new AppModel({
      _moduleOptions: {
        test4: {
          foo: 'bar'
        }
      }
    }, {parse: true})
    model.setModel('test4', Backbone.Model)
    chai.expect(model._children.test4).to.be.a('object')
    chai.expect(model._children.test4._modelOptions.foo).to.equal('bar')
  })
})
