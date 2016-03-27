var MainView = require('app/views/mainView')
var chai = require('chai')

describe('MainView spec', function () {
  context('instantiation', function () {
    it('can be insatntiatied', function () {
      chai.expect(new MainView()).to.be.a('object')
    })
  })
})
