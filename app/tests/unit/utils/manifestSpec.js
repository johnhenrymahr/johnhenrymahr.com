var manifest = require('app/utils/_manifest')
var chai = require('chai')
manifest.json = {
  sections: [
    {
      id: 'test1',
      template: 'testTemplate'
    },
    {
      children: [
        {
          id: 'test2',
          template: 'testTemplate2'
        },
        {
          id: 'test3',
          template: 'testTemplate3'
        }
      ]
    }
  ]
}
describe('manifset util spec', function () {
  it('can get sections ', function () {
    chai.expect(manifest.json.sections).to.be.a('array')
  })
  it('can get child data', function () {
    chai.expect(manifest.get('test2').template).to.equal('testTemplate2')
  })
  it('can get top level section data', function () {
    chai.expect(manifest.get('test1').template).to.equal('testTemplate')
  })
  it('returns undefined when id is not matched', function () {
    chai.expect(manifest.get('notthere')).to.be.undefined
  })
})
