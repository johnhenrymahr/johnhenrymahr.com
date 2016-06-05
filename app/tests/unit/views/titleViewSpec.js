var TitleView = require('app/views/titleView')
var chai = require('chai')

describe('title view ', function () {
  it('can be instantiated ', function () {
    chai.expect(new TitleView()).to.be.a('object')
  })
})
