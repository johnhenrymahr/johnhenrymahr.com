var chai = require('chai')
var ContactModel = require('app/models/contactModel')
var Backbone = require('backbone')
var sinon = require('sinon')

describe('contact model spec', function () {
  var sandbox
  var model
  beforeEach(function () {
    sandbox = sinon.sandbox.create()
    model = new ContactModel()
  })
  afterEach(function () {
    sandbox.restore()
    model = null
  })
  context('contactModel: instantiation', function () {
    it('can be instantiated', function () {
      chai.expect(model).to.be.a('object')
      chai.expect(model).to.be.instanceof(Backbone.Model)
    })
  })
  context('contactModel: validation', function () {
    it('validates empty name', function () {
      model.set('name', '', {validate: true})
      chai.expect(model.validationError.name).to.equal('Name is a required field.')
    })
    it('validates special chars in name field', function () {
      model.set('name', 'another&', {validate: true})
      chai.expect(model.validationError.name).to.equal('Field contains unrecognized characters.')
    })
    it('passes valid name', function () {
      model.set('name', 'another', {validate: true})
      chai.expect(model.validationError).to.equal(null)
    })
    it('validates special chars in company field', function () {
      model.set('company', 'another&', {validate: true})
      chai.expect(model.validationError.company).to.equal('Field contains unrecognized characters.')
    })
    it('validates a in-valid phone number', function () {
      model.set('phoneNumber', '612-134-9899', {validate: true})
      chai.expect(model.validationError.phoneNumber).to.equal('Please enter a valid phone number.')
    })
    it('validates a in-valid phone number', function () {
      model.set('phoneNumber', '534-9899', {validate: true})
      chai.expect(model.validationError.phoneNumber).to.equal('Please enter a valid phone number.')
    })
    it('validates a valid phone number', function () {
      model.set('phoneNumber', '612-834-9899', {validate: true})
      chai.expect(model.validationError).to.equal(null)
    })
    it('validates a valid phone number', function () {
      model.set('phoneNumber', '(612) 834-9899', {validate: true})
      chai.expect(model.validationError).to.equal(null)
    })
    it('validates an empty email', function () {
      model.set('email', '', {validate: true})
      chai.expect(model.validationError.email).to.equal('E-mail address is a required field.')
    })
    it('validates an invalid email', function () {
      model.set('email', 'jasfadsf', {validate: true})
      chai.expect(model.validationError.email).to.equal('Please enter a valid e-mail address.')
    })
    it('validates an valid email', function () {
      model.set('email', 'joe@email.com', {validate: true})
      chai.expect(model.validationError).to.equal(null)
    })
    it('creates composite errors', function () {
      model.set({name: 'joe&', email: 'lacy'}, {validate: true})
      chai.expect(model.validationError.email).to.equal('Please enter a valid e-mail address.')
      chai.expect(model.validationError.name).to.equal('Field contains unrecognized characters.')
    })
  })
})
