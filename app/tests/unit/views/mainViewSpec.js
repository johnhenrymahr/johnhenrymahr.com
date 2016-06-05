var App = require('app/app')
var MainView = require('app/views/mainView')
var chai = require('chai')
var $ = require('jquery')

App.model = {
  getModel: function () {}
}

describe('MainView spec', function () {
  context('instantiation', function () {
    it('can be insatntiatied', function () {
      chai.expect(new MainView()).to.be.a('object')
    })
  })
  context('_getSections ', function () {
    var view = new MainView()
    var secs = view._getSections()
    it('returns  array', function () {
      chai.expect(secs).to.be.a('array')
    })
    it('retursn jQUERY obejcts', function () {
      chai.expect(secs[0]).to.be.an.instanceof($)
    })
  })
})
