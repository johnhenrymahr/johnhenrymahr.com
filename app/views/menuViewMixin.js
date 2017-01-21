var _ = require('lodash')
var itemHandler = require('app/utils/menuItemHandler')
module.exports = _.extend({}, itemHandler, {
  events: {
    'click .menu__item': 'handleMenuClick'
  }
})
