var chai = require('chai')
var decorator = require('app/utils/decorator')
var Backbone = require('backbone')
var View = Backbone.View.extend(decorator)
var view = new View()

describe('decorator spec', function () {
  context('decorator method', function () {
    var el
    beforeEach(function () {
      el = $('<div><input class="form-control" /></div>')
      view.decorate(el, 'test message')
    })
    it('popultes message text', function () {
      chai.expect(el.find('span.validation-error').text()).to.equal(' test message')
    })
    it('puts span in correct location in DOM', function () {
      chai.expect(el.find('input').next()[0].tagName).to.equal('SPAN')
    })
    it('puts span with correct class in correct location in DOM', function () {
      chai.expect(el.find('input').next().attr('class')).to.equal('validation-error')
    })
    it('adds class to container', function () {
      chai.expect(el.hasClass('has-error')).to.be.true
    })
  })
  context('undecorator method', function () {
    var el
    beforeEach(function () {
      el = $('<div class="has-error"><input /><span class="validation-error"></span></div>')
      view.undecorate(el)
    })
    it('removes error class', function () {
      chai.expect(el.hasClass('has-error')).to.be.false
    })
    it('removes error span', function () {
      chai.expect(el.find('span.validation-error').length).to.equal(0)
    })
  })
})
