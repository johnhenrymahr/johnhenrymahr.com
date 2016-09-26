var _ = require('lodash')
var viewManifest = require('data/viewManifest.json')
module.exports = {
  json: viewManifest,
  has: function (prop) {
    return _.has(this.json, prop)
  },
  get: function (id) {
    return _.reduce(this.json.sections, function (result, value, key) {
      if (_.isUndefined(result)) {
        if (_.has(value, 'id') && value.id === id) {
          result = value
        } else if (_.isArray(value.children)) {
          result = _.find(value.children, {'id': id})
        }
      }
      return result
    }, undefined)
  }
}
