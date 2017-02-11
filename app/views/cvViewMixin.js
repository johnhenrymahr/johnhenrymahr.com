var _ = require('lodash')
var decorator = require('../utils/decorator')
var App = require('app/app')
module.exports = _.extend(decorator, {
  initialize: function (options) {
    if (_.has(options, 'model')) {
      this.listenTo(options.model, 'invalid', _.bind(this.onValidationError, this))
      this.listenTo(options.model, 'sync', function () {
        App.vent.trigger('app:track', 'cv-request-refs', 'cv:submit:success', 'form-submit')
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
  events: {
    'click .cv__ref--request': 'onRequestClick',
    'submit .cv__form': 'onFormSubmit',
    'click .cv__form--cancel': 'onFormCancel',
    'blur .form-group .form-control:visible': 'onInputBlur',
    'focus .form-group .form-control:visible': 'onInputFocus'
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
      $ref.slideUp('slow', function () {
        $ref.get(0).focus()
      })
    } else {
      $ref.slideDown('slow', function () {
        $ref.find('input:first').get(0).focus()
      })
    }
  },
  onRequestClick: function (e) {
    e.preventDefault()
    this.togglePopover()
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
  onFormCancel: function (e) {
    e.preventDefault()
    this.model.clear()
    this.$('.form-control:visible').each(_.bind(function (idx, ele) {
      var $ele = $(ele)
      $ele.val('')
      this.undecorate($ele.parent())
    }, this))
    this.togglePopover()
  }
})
