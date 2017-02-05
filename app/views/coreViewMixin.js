var _ = require('lodash')
var itemHandler = require('app/utils/menuItemHandler')
var App = require('app/app')
module.exports = _.extend({}, itemHandler, {
  initialize: function (options) {
    this._registerPromise()
    App.setState('core:expanded', false)
    this.bindExpand()
    this.scrollDownOnce = _.once(this.scrollDown)
    this.listenTo(App.vent, 'scroll:collapse', _.bind(this._collapse, this))
  },
  events: {
    'click .core__connect--control': 'handleMenuClick',
    'click .arrow>a ': 'scrollDown',
    'click .core__content': 'scrollDown'
  },
  bindExpand: function () {
    this.listenToOnce(App.vent, 'scroll:expand', _.bind(this._expand, this))
  },
  scrollDown: function (e) {
    if (_.isObject(e) && _.isFunction(e.preventDefault)) {
      e.preventDefault()
    }
    this.scrollToElement(this.$('.core__wrapper'))
  },
  onAppReady: function () {
    this.coreAnimation()
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
    this.$('.core__wrapper, .core__title, .core__banner').removeClass('hidden')
    _.delay(_.bind(function () {
      this.transitionEnd(this.$el, 750, _.bind(function () {
        this.stepsAnimation()
        this.contentAnimation()
      }, this))
      this.$el.addClass('active')
    }, this), 1200)
  },
  _bindTransitionEnd: function ($ele, expanded) {
    if (_.isFunction(this.transitionEnd)) {
      this.transitionEnd($ele, 1200, _.bind(function () {
        App.setState('core:expanded', expanded)
        if (expanded) {
          App.vent.trigger('core:expanded')
          this.$('.core__connect').removeClass('fadeOut')
          this.$('.core__content').removeClass('pointer')
          _.defer(function () {
            App.vent.trigger('scroll:enable')
          })
        } else {
          this._registerPromise()
          this.bindExpand()
          this.$('.core__content').addClass('pointer')
        }
      }, this))
    }
  },
  _registerPromise: function () {
    App.registerVentPromise('core:expanded')
  },
  _expand: function () {
    App.vent.trigger('scroll:disable')
    this.scrollDownOnce()
    var $ele = this.$('.core__content--body')
    this._bindTransitionEnd($ele, true)
    $ele.addClass('expanded')
    this.$('.core__banner').addClass('shifted')
    this.$('.arrow').addClass('fadeOut').removeClass('bounce')
  },
  _collapse: function () {
    var $ele = this.$('.core__content--body')
    this._bindTransitionEnd($ele, false)
    $ele.removeClass('expanded')
    this.$('.core__banner').removeClass('shifted')
    this.$('.arrow').removeClass('fadeOut').addClass('bounce')
  },
  onPostRender: function () {
    this.$('.arrow>a').get(0).focus()
  }
})
