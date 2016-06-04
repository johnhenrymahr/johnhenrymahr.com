var buildElement = require('app/utils/buildContainer')
var chai = require('chai')
describe('buildElement function', function () {
  it('can build a default element ', function () {
    chai.expect(buildElement().wrap('<div />').parent().html()).to.equal('<div></div>')
  })
  it('can add a class ', function () {
    chai.expect(buildElement({attributes: {className: 'test1'}}).hasClass('test1')).to.be.true
  })
  it('can add attributes', function () {
    var e = buildElement({attributes: {id: 'test', name: 'joe'}})
    chai.expect(e.attr('name')).to.equal('joe')
    chai.expect(e.attr('id')).to.equal('test')
  })
  it('can build custom tag name', function () {
    chai.expect(buildElement({tagName: 'span'}).wrap('<div />').parent().html()).to.equal('<span></span>')
  })
  it('can add attributes and classNames with custom tagName ', function () {
    var e = buildElement({tagName: 'span', attributes: {id: 'test', name: 'joe', className: 'time love'}})
    chai.expect(e.attr('name')).to.equal('joe')
    chai.expect(e.attr('id')).to.equal('test')
    chai.expect(e.hasClass('time')).to.be.true
    chai.expect(e.hasClass('love')).to.be.true
    chai.expect(e.get(0).tagName).to.equal('SPAN')
  })
})
