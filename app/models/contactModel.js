var _ = require('lodash')
var Backbone = require('backbone')
module.exports = Backbone.Model.extend({
  url: '/api/',
  log: function () {
    if (console && _.isFunction(console.log) && window.appLogging) {
      console.log.apply(this, arguments)
    }
  },
  validate: function (atts, options) {
    var errors = {}
    _.forEach(atts, _.bind(function (value, key) {
      _.forEach(_.filter(this.validations, {attribute: key}), _.bind(function (validation) {
        if (validation.validate.call(this, value) === false) {
          errors[key] = validation.message
        }
      }, this))
    }, this))
    return (!_.isEmpty(errors)) ? errors : undefined
  },
  defaults: function () {
    return {
      component: 'contact'
    }
  },
  save: function (options) {
    this.trigger('request')
    return Backbone.$.ajax({
      url: this.url,
      data: this.toJSON(),
      method: 'POST',
      type: 'POST'
    })
    .done(_.bind(function (resp) {
      this.trigger('sync', this, resp, options)
    }, this))
    .fail(_.bind(function (resp) {
      this.trigger('error', this, resp, options)
    }, this))
  },
  validations: [
    {
      attribute: 'name',
      validate: function (name) {
        return Boolean(name.length)
      },
      message: 'Name is a required field.'
    },
    {
      attribute: 'name',
      validate: function (name) {
        return (name.length) ? (/^[A-Za-z0-9-.'’ ]*$/).test(name) : true
      },
      message: 'Field contains unrecognized characters.'
    },
    {
      attribute: 'company',
      validate: function (company) {
        return (company.length) ? (/^[A-Za-z0-9-.'’ ]*$/).test(company) : true
      },
      message: 'Field contains unrecognized characters.'
    },
    {
      attribute: 'phone',
      validate: function (phone) {
        return (phone.length)
          ? (/^\([2-9][0-9]{2}\)[ ]*[2-9][0-9]{2}[ ]*[-]*[ ]*[0-9]{4}$|^[2-9][0-9]{2}[ ]*[.-]*[ ]*[2-9][0-9]{2}[ ]*[.-]*[ ]*[0-9]{4}$/).test(phone)
          : true
      },
      message: 'Please enter a valid phone number.'
    },
    {
      attribute: 'email',
      validate: function (email) {
        return Boolean(email.length)
      },
      message: 'E-mail address is a required field.'
    },
    {
      attribute: 'email',
      validate: function (email) {
        return (email.length)
          ? (/^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/).test(email)
          : true
      },
      message: 'Please enter a valid e-mail address.'
    },
    {
      attribute: 'topic',
      validate: function (topic) {
        return Boolean(topic.length)
      },
      message: 'Please select a topic.'
    },
    {
      attribute: 'custom-topic',
      validate: function (topic) {
        return (this.get('topic') === 'other') ? Boolean(topic.length) : true
      },
      message: 'Please enter a custom topic.'
    },
    {
      attribute: 'message',
      validate: function (message) {
        return Boolean(message.length)
      },
      message: 'Please provide details.'
    }
  ]
})
