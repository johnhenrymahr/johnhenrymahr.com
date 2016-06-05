var manifest = require('app/utils/_manifest')
var chai = require('chai')
describe('manifset util spec', function () {
  it('can get sections ', function () {
    chai.expect(manifest.json.sections).to.be.a('array')
  })
  it('can get data ', function () {
    chai.expect(manifest.get('title').template).to.equal('titleTpl.dust')
  })
  it('returns undefined when node is not there', function () {
    chai.expect(manifest.get('notthere')).to.be.undefined
  })
})
