var Backbone = require('backbone')
var techList = require('data/techlist.json')
module.exports = Backbone.Model.extend({
  toJSON: function () {
    return {
      tech: techList
    }
  }
})
