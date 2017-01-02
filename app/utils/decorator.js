module.exports = {
  decorate: function ($el, message) {
    $el.addClass('has-error')
    $el
      .find('.validation-error')
      .remove()
      .end()
      .find('.form-control')
      .after('<span class="validation-error"><i class="glyphicon glyphicon-warning-sign"></i> ' + message + '</span>')
  },
  undecorate: function ($el) {
    $el.find('.validation-error').remove()
    $el.removeClass('has-error')
  }
}
