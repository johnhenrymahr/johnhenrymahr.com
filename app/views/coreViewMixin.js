var _ = require('lodash')
module.exports = {
  onAppReady: function () {
    this.coreAnimation()
    this.bindScrollHandler()
  },
  stepsAnimation: function () {
    var $ele = this.$('.core__steps')
    $ele
    .removeClass('hidden')
    .delay(100)
    .queue(_.bind(function () {
      $ele
        .addClass('slideIn')
        .dequeue()
    }, this))
    .delay(1100)
    .queue(_.bind(function () {
      $ele
        .removeClass('slideIn')
        .addClass('slideOut')
        .dequeue()
    }, this))
  },
  contentAnimation: function () {
    var $ele = this.$('.core__content')
    $ele
    .delay(300)
     .queue(_.bind(function () {
       $ele
          .find('.core__content--flash')
          .addClass('active')
          .end()
          .find('.core__content--icon')
          .addClass('slideIn')
          .end()
          .dequeue()
     }, this))
     .delay(500)
     .queue(_.bind(function () {
       $ele.find('.core__content--icon')
          .addClass('active')
          .end()
          .dequeue()
     }, this))
  },
  coreAnimation: function () {
    console.log('run core animation')
    _.delay(_.bind(function () {
      this.transitionEnd(this.$el, 750, _.bind(function () {
        this.stepsAnimation()
        this.contentAnimation()
      }, this))
      this.$el.addClass('active')
    }, this), 1200)
  },
  bindScrollHandler: function () {
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
