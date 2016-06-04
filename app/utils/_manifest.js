var _ = require('lodash')
var viewManifest = require('data/viewManifest.json')
module.exports = {
  json: viewManifest,
  get: function (id) {
    var result
    _.each(this.json.sections, function (section) {
      if (_.has(section, 'children')) {
        result = _.find(section.children, {id: id})
      }
    })
    return result
  }
}
