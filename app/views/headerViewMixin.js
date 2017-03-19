var _ = require('lodash')
var App = require('app/app')
module.exports = {
  initialize: function () {
    this.listenTo(App.vent, 'core:animationEnd', _.bind(function () {
      _.delay(_.bind(function () {
        this.$el.addClass('active')
      }, this), 700)
    }, this))

    this.listenTo(App.router, 'route', _.bind(function (route) {
      this.$('a').removeClass('nav__item--selected')
      this.$('a[href$=' + route + ']').addClass('nav__item--selected')
    }, this))
  }
}
