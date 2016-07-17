var Backbone = require('backbone')
module.exports = Backbone.Model.extend({
  initialize: function (atts, options) {},
  getModel: function (id) {
    if (this[id] instanceof Backbone.Model) {
      return this[id]
    }
    return null
  },
  parse: function (response) {
    return response
  }
})
