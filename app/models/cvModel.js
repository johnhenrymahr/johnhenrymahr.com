var contactModel = require('../models/contactModel')

module.exports = contactModel.extend({
  defaults: function () {
    return {
      component: 'cv'
    }
  }
})
