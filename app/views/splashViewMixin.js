// var $ = require('jQuery')
var _ = require('lodash')
module.exports = {
  onAttach: function () {
    /*
    $(window).one('scroll', function (e) {
      if ($(document).scrollTop() > 0) {
        this.softRemove()
      }
    }.bind(this))
    */
  },

  softRemove: function () {
    this.transitionEnd(this.$el, 2000, _.bind(function () {
      this.remove()
    }, this))
    this.$el.addClass('hidden')
  }

}
