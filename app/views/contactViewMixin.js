var _ = require('lodash')
module.exports = {
  events: {
    'blur .form-group .form-control:visible': 'onInputBlur',
    'change .form-group select.form-control': 'onInputChange',
    'focus .form-group .form-control:visible': 'onInputFocus',
    'submit form': 'onFormSubmit'
  },
  initialize: function (options) {
    if (_.has(options, 'model')) {
      this.listenTo(options.model, 'invalid', _.bind(this.onValidationError, this))
    }
  },
  onValidationError: function (model, error, options) {
    if (_.isObject(error) && !_.isEmpty(error)) {
      _.each(error, _.bind(function (message, name) {
        var $el = this.$('[name=' + name + ']').parents('.form-group')
        if ($el.length) {
          this.decorate($el, message)
        }
      }, this))
    }
  },
  onInputChange: function (e) {
    var $el = this.$(e.currentTarget)
    if ($el.val().length) {
      $el.find('option[value=""]').attr('disabled', 'disabled')
    }
    if ($el.val() === 'other') {
      $el.parent().next().removeClass('form-group-hidden')
    } else {
      $el.parent().next().addClass('form-group-hidden')
    }
  },
  onInputFocus: function (e) {
    var $el = this.$(e.currentTarget).parent()
    this.undecorate($el)
  },
  onInputBlur: function (e) {
    var $el = this.$(e.currentTarget)
    this.model.set($el.attr('name'), $el.val(), {validate: true})
  },
  onFormSubmit: function (e) {
    e.preventDefault()
    var fields = {}
    this.$('.form-control:visible').each(_.bind(function (idx, ele) {
      var $ele = $(ele)
      fields[$ele.attr('name')] = $ele.val()
    }, this))
    this.model.set(fields)
    if (this.model.isValid()) {
    }
  },
  decorate: function ($el, message) {
    $el.addClass('has-error')
    $el
      .find('.validation-error')
      .remove()
      .end()
      .find('.form-control')
      .after('<span class="validation-error"><i class="glyphicon glyphicon-warning-sign"></i> ' + message + '</span>')
  },
  undecorate: function ($el) {
    $el.find('.validation-error').remove()
    $el.removeClass('has-error')
  }
}
