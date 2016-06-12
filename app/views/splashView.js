var View = require('app/views/_baseView')
var conf = require('app/utils/_manifest').get('splash')
require('app/less/splash.less')
if (conf) {
  module.exports = View.extend({
    template: require('app/dust/' + conf.template)
  })
}
