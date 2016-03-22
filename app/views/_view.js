var _ = require('lodash')
var Backbone = require('backbone')
module.exports = Backbone.View.extend({
  serializeModel: function () {
    return (this.model) ? this.model.toJSON() : {}
  },
  _getData: function () {
    var data = this.serializeModel()
    if (this.templateHelpers) {
      _.merge(data, _.result(this, 'templateHelpers'))
    }
    return data
  },
  render: function () {
    if (_.isFunction(this.template)) {
      this.template(this._getData(), function (err, html) {
        if (err === null) {
          this.$el.html(html)
          return this
        }
      }.bind(this))
    }
  }
})
