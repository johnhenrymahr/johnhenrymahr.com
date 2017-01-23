var _ = require('lodash')
var itemHandler = require('app/utils/menuItemHandler')
var App = require('app/app')
module.exports = _.extend({}, itemHandler, {
  initialize: function () {
    this._registerPromsie()
    App.setState('core:expanded', false)
  },
  onAttach: function () {
    this.$('.core__wrapper, .core__title, .core__banner').addClass('fadeOut')
  },
  events: {
    'click .core__connect--control': 'handleMenuClick'
  },
  onAppReady: function () {
    this.coreAnimation()
    this.listenTo(App.vent, 'core:expand', _.bind(this._expand, this))
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
    $('html, body').scrollTop(0)
    _.delay(_.bind(function () {
      this.transitionEnd(this.$el, 750, _.bind(function () {
        this.stepsAnimation()
        this.contentAnimation()
        this.bindScrollHandler()
        this.$('.core__wrapper, .core__title, .core__banner').removeClass('fadeOut')
      }, this))
      this.$el.addClass('active')
    }, this), 1200)
  },
  bindScrollHandler: function () {
    $(window).on('scroll', _.bind(_.debounce(function (e) {
      if ($(document).scrollTop() > 0) {
        this._expand()
      }
      if ($(document).scrollTop() === 0) {
        this._collapse()
      }
    }, 250, {leading: true}), this))
  },
  _bindTransitionEnd: function ($ele, expanded) {
    if (_.isFunction(this.transitionEnd)) {
      this.transitionEnd($ele, 1200, _.bind(function () {
        App.setState('core:expanded', expanded)
        if (expanded) {
          App.vent.trigger('core:expanded')
        } else {
          this._registerPromsie()
        }
      }, this))
    }
  },
  _registerPromsie: function () {
    App.registerVentPromise('core:expanded')
  },
  _expand: function () {
    var $ele = this.$('.core__content--body')
    this._bindTransitionEnd($ele, true)
    $ele.addClass('expanded')
    this.$('.core__banner').addClass('shifted')
  },
  _collapse: function () {
    var $ele = this.$('.core__content--body')
    this._bindTransitionEnd($ele, false)
    $ele.removeClass('expanded')
    this.$('.core__banner').removeClass('shifted')
  }
})
