var manifest = require('app/utils/_manifest')
var chai = require('chai')

describe('manifset util spec', function () {
  it('can get sections ', function () {
    chai.expect(manifest.json.sections).to.be.a('array')
  })
})
