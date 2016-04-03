var _ = require('lodash')
var viewManifest = require('data/viewManifest.json')
module.exports = {
  json: viewManifest,
  get: function (id) {
    return _.find(this.json.children, {id: id})
  }
}
