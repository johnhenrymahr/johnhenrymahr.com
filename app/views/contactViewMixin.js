var _ = require('lodash')
var App = require('app/app')
var decorator = require('app/utils/decorator')
var formatPhoneNumber = require('app/utils/formatPhoneNumber')
module.exports = _.extend({}, decorator, {
  events: function () {
    return {
      'blur .contact__form .form-group .form-control:visible': 'onInputBlur',
      'change .contact__form .form-group select.form-control': 'onInputChange',
      'focus .contact__form .form-group .form-control:visible': 'onInputFocus',
      'click .contact__form__clear': 'onFormClear',
      'submit .contact__form': 'onFormSubmit'
    }
  },
  initialize: function (options) {
    if (_.has(options, 'model')) {
      window.contactModel = options.model
      this.listenTo(options.model, 'invalid', _.bind(this.onValidationError, this))
      this.listenTo(options.model, 'sync', _.bind(function () {
        App.vent.trigger('app:track', 'contact-form', 'contact:submit:success', 'form-submit')
        this.render({_data: { state: 'success' }})
        this.slideToFormTop()
      }, this))
      this.listenTo(options.model, 'error', _.bind(function () {
        this.render({_data: {submitError: true}})
        this.slideToFormTop()
      }, this))
      this.listenTo(options.model, 'change:phone', _.bind(function (model, value, options) {
        this.$('input[name=phone]').val(formatPhoneNumber(value))
      }, this))
      this.listenTo(options.model, 'request', _.bind(function () {
        this.render({_data: {state: 'submitting'}})
        this.slideToFormTop()
      }, this))
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
  templateHelpers: {
    state: '',
    submitError: false
  },
  slideToFormTop: function () {
    $('html, body').animate({
      scrollTop: ((this.$('.contact__formContainer').offset().top || 30) - 30)
    })
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
  onFormClear: function (e) {
    e.preventDefault()
    this.$('input:visible, textarea, select', this.$('.contact__form')).not('[type=submit]').each(_.bind(function (idx, ele) {
      var $ele = $(ele)
      $ele.val('')
      this.model.unset($ele.attr('name'))
      if ($ele.prop('tagName') === 'SELECT') {
        $ele.find('option[value=""]').removeAttr('disabled')
      }
    }, this))
  },
  onFormSubmit: function (e) {
    e.preventDefault()
    var fields = {}
    this.$('.form-control:visible', this.$('.contact__form')).each(_.bind(function (idx, ele) {
      var $ele = $(ele)
      fields[$ele.attr('name')] = $ele.val()
    }, this))
    this.model.set(fields)
    if (this.model.isValid()) {
      this.model.save()
    }
  }
})
