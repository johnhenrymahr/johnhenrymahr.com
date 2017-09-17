var _ = require('lodash')
var App = require('app/app')
module.exports = {
  events: {
    'click .arrow>a ': 'handleArrowClick'
  },
  onAppReady: function () {
    this.coreAnimation()
    if (App.oldIE) {
      this.oldIE()
    }
  },
  oldIE: function () {
    this.$el.addClass('oldIE')
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
       this._coreAnimationComplete()
     }, this))
  },
  coreAnimation: function () {
    this.log('run core animation')
    this.$('.core__wrapper, .core__banner').removeClass('hidden')
    _.delay(_.bind(function () {
      this.transitionEnd(this.$el, 750, _.bind(function () {
        this.stepsAnimation()
        this.contentAnimation()
      }, this))
      this.$el.addClass('active')
    }, this), 1200)
  },
  _coreAnimationComplete: function () {
    this.$('.arrow').removeClass('fadeOut').addClass('bounce')
    this.$('.core__title--container').removeClass('stageRight')
    App.vent.trigger('core:animationEnd')
  },
  onPostRender: function () {
    this.$('.arrow>a').get(0).focus()
  }
}
