module.exports = {
  onAttach: function () {
    $(window).on('scroll', function (e) {
      if ($(document).scrollTop() > 0) {
        this.$el.addClass('active')
      }
      if ($(document).scrollTop() === 0) {
        this.$el.removeClass('active')
      }
    }.bind(this))
  }
}
