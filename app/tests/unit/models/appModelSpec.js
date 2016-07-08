var chai = require('chai')
var AppModel = require('app/models/appModel')
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
  it('can be instantiated', function () {
    chai.expect(model).to.be.a('object')
    chai.expect(model).to.be.instanceOf(Backbone.Model)
  })
  it('getModel returns a model when property is a model', function () {
    model.tester = new Backbone.Model()
    chai.expect(model.getModel('tester')).to.be.instanceOf(Backbone.Model)
  })
  it('getModel returns null when no model is found', function () {
    chai.expect(model.getModel('tester')).to.be.null
  })
})
