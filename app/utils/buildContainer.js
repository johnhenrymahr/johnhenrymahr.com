var $ = require('jquery')
var _ = require('lodash')
module.exports = function buildElement (config) {
  config = _.isObject(config) ? config : {}
  _.defaults(config, {
    tagName: 'div'
  })
  var element = $('<' + config.tagName + ' />')
  if (_.isObject(config.attributes)) {
    if (_.has(config.attributes, 'className')) {
      element.addClass(config.attributes.className)
      delete config.attributes.className
    }
    if (!_.isEmpty(config.attributes)) {
      element.attr(config.attributes)
    }
  }
  return element
}
