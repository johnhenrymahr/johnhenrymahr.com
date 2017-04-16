var _ = require('lodash')
var App = require('app/app')
var decorator = require('app/utils/decorator')
module.exports = _.extend({}, decorator, {
  events: {
    'blur .form-group .form-control:visible': 'onInputBlur',
    'change .form-group select.form-control': 'onInputChange',
    'focus .form-group .form-control:visible': 'onInputFocus',
    'submit form': 'onFormSubmit'
  },
  initialize: function (options) {
    if (_.has(options, 'model')) {
      this.listenTo(options.model, 'invalid', _.bind(this.onValidationError, this))
      this.listenTo(options.model, 'sync', function () {
        App.vent.trigger('app:track', 'contact-form', 'contact:submit:success', 'form-submit')
      })
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
  }
})
