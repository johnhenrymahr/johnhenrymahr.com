var _ = require('lodash')
var decorator = require('../utils/decorator')
var App = require('app/app')
var formatPhoneNumber = require('app/utils/formatPhoneNumber')
var $ = require('jquery')
module.exports = _.extend({}, decorator, {
  initialize: function (options) {
    if (_.has(options, 'model')) {
      this.listenTo(options.model, 'invalid', _.bind(this.onValidationError, this))
      this.listenTo(options.model, 'sync', function () {
        App.vent.trigger('app:track', 'cv-request-refs', 'cv:submit:success', 'form-submit')
        this.$('form, .cv__popover--subtitle, .cv__popover--spinner, .alert-danger').addClass('hidden')
        this.$('.alert-success').removeClass('hidden')
        this.slideToPop()
      })
      this.listenTo(options.model, 'change:phone', _.bind(function (model, value, options) {
        this.$('input[name=phone]').val(formatPhoneNumber(value))
      }, this))
      this.listenTo(options.model, 'error', _.bind(function () {
        this.$('.cv__popover--spinner').addClass('hidden')
        this.$('form, .cv__popover--subtitle').removeClass('hidden')
        this.$('.alert-danger').removeClass('hidden')
        this.slideToPop()
      }, this))
      this.listenTo(options.model, 'request', _.bind(function () {
        this.$('form, .cv__popover--subtitle, .alert-danger').addClass('hidden')
        this.$('.cv__popover--spinner').removeClass('hidden')
        this.slideToPop()
      }, this))
    }
  },
  templateHelpers: {
    state: '',
    submitError: false
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
  events: function () {
    return {
      'click .cv__ref--request': 'onRequestClick',
      'submit .cv__form': 'onFormSubmit',
      'click .cv__form .cv__form--cancel': 'onFormCancel',
      'blur .cv__form .form-group .form-control:visible': 'onInputBlur',
      'focus .cv__form .form-group .form-control:visible': 'onInputFocus'
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
  togglePopover: function () {
    var $ref = this.$('.cv__popover')
    if ($ref.is(':visible')) {
      $ref.addClass('hidden')
      this.slideBack()
    } else {
      $ref.removeClass('hidden')
      this.slideToPop()
    }
  },
  slideBack: function () {
    if (this.scrollTop && _.isNumber(this.scrollTop)) {
      $('html, body').animate({
        scrollTop: this.scrollTop
      })
    }
  },
  slideToPop: function () {
    this.scrollTop = $('html, body').scrollTop()
    $('html, body').animate({
      scrollTop: this.$('.cv__ref').offset().top
    })
  },
  onRequestClick: function (e) {
    e.preventDefault()
    this.togglePopover()
  },
  onFormSubmit: function (e) {
    e.preventDefault()
    var fields = {}
    this.$('.form-control:visible', this.$('.cv__form')).each(_.bind(function (idx, ele) {
      var $ele = $(ele)
      fields[$ele.attr('name')] = $ele.val()
    }, this))
    this.model.set(fields)
    if (this.model.isValid()) {
      this.model.save()
    }
  },
  onFormCancel: function (e) {
    e.preventDefault()
    this.$('.form-control:visible', this.$('.cv__form')).each(_.bind(function (idx, ele) {
      var $ele = $(ele)
      $ele.val('')
      this.model.unset($ele.attr('name'))
      this.undecorate($ele.parent())
    }, this))
    this.togglePopover()
  }
})
