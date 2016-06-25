var SplashView = require('app/views/titleView')
var chai = require('chai')

describe('title view ', function () {
  it('can be instantiated ', function () {
    chai.expect(new SplashView()).to.be.a('object')
  })
})
