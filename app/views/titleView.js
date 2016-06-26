var View = require('app/views/_baseView')
var conf = require('app/utils/_manifest').get('title')
require('app/less/title.less')
require('app/less/icons.less')
if (conf) {
  module.exports = View.extend({
    template: require('app/dust/' + conf.template)
  })
}
