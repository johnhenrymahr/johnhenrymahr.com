var Backbone = require('backbone')
module.exports = Backbone.Model.extend({
  initialize: function (atts, options) {},
  getModel: function (model) {
    if (this[model] instanceof Backbone.Model) {
      return this[model]
    }
    return null
  }
})
