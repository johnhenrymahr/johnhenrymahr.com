var Backbone = require('backbone')
var _ = require('lodash')
var moduleDataCache = {}
var moduleOptionsCache = {}
module.exports = Backbone.Model.extend({
  _children: {},
  setModel: function (key, Model) {
    var atts = {}
    if (_.has(moduleDataCache, key) && _.isObject(moduleDataCache[key])) {
      atts = moduleDataCache[key]
    }
    var options = {}
    if (_.has(moduleOptionsCache, key) && _.isObject(moduleOptionsCache[key])) {
      options = moduleOptionsCache[key]
    }
    this._children[key] = new Model(atts, _.assign({parse: true}, options))
    this._children[key]._modelOptions = options
  },
  getModel: function (key) {
    if (this._children[key] instanceof Backbone.Model) {
      return this._children[key]
    }
    return null
  },
  parse: function (response) {
    if (_.has(response, '_moduleData') && _.isObject(response._moduleData)) {
      moduleDataCache = response._moduleData
      delete response._moduleData
    }
    if (_.has(response, '_moduleOptions') && _.isObject(response._moduleOptions)) {
      moduleOptionsCache = response._moduleOptions
      delete response._moduleOptions
    }
    return response
  }
})
