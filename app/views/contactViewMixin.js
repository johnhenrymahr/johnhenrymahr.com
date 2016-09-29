var _ = require('lodash')
module.exports = {
  events: {
    'blur .form-group input': 'onInputBlur',
    'focus .form-group input': 'onInputFocus'
  },
  initialize: function (options) {
    if (_.has(options, 'model')) {
      this.listenTo(options.model, 'invalid', _.bind(this.onValidationError, this))
    }
  },
  onValidationError: function (model, error, options) {
    if (_.isObject(error) && !_.isEmpty(error)) {
      _.each(error, _.bind(function (message, name) {
        var $el = this.$('[name=' + name + ']')
        if ($el.length) {
          this.decorate($el, message)
        }
      }, this))
    }
  },
  onInputFocus: function (e) {
    var $el = this.$(e.currentTarget)
    this.undecorate($el)
  },
  onInputBlur: function (e) {
    var $el = this.$(e.currentTarget)
    this.model.set($el.attr('name'), $el.val(), {validate: true})
  },
  decorate: function ($el, message) {
    $el.addClass('has-error')
    $el.find('input')
    .after('<span class="validation-error"><i class="glyphicon glyphicon-warning-sign"></i>' + message + '</span>')
  },
  undecorate: function ($el) {
    $el.find('.validation-error').remove()
    $el.removeClass('has-error')
  }
}
