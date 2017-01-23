var App = require('app/app')
var chai = require('chai')

describe('app spec', function () {
  context('state vars ', function () {
    it('can set a state var', function () {
      App.setState('foo', 'bar')
      chai.expect(App._stateVars['foo']).to.equal('bar')
    })
    it('can get a state var', function () {
      App.setState('foo', 'bar')
      chai.expect(App.getState('foo')).to.equal('bar')
    })
    it('retun sundefined for unknown state var', function () {
      App.setState('foo', 'bar')
      chai.expect(App.getState('unexpected')).to.equal(undefined)
    })
  })
})
