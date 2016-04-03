var View = require('./_view')
var conf = require('./_manifest').get('title')
module.exports = View.extend({
  template: require('app/dust/' + conf.template)
})
