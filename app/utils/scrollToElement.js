module.exports = {
  scrollToElement: function ($ele, $focusEle) {
    $focusEle = $focusEle || $ele
    if (!($ele instanceof $)) {
      return
    }
    $('html, body').animate({
      scrollTop: $ele.offset().top
    }, 1200, 'swing', function () {
      $focusEle.get(0).focus()
    })
  }
}
