var _ = require('lodash')
module.exports = {
  initialize: function () {
    this.once('view:attach', _.bind(this.stepsAnimation, this))
  },
  stepsAnimation: function () {
    this.$('.core__steps')
    .delay(800)
    .queue(_.bind(function () {
      this.$('.core__steps')
        .addClass('slideIn')
        .dequeue()
    }, this))
    .delay(1100)
    .queue(_.bind(function () {
      this.$('.core__steps')
        .removeClass('slideIn')
        .addClass('slideOut')
        .dequeue()
    }, this))
  },
  onAttach: function () {
    $(window).on('scroll', function (e) {
      if ($(document).scrollTop() > 0) {
        this.$('.core__content--body').addClass('expanded')
        this.$('.core__banner').addClass('shifted')
      }
      if ($(document).scrollTop() === 0) {
        this.$('.core__content--body').removeClass('expanded')
        this.$('.core__banner').removeClass('shifted')
      }
    }.bind(this))
  }
}
