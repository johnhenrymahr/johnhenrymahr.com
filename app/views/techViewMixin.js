var $ = require('jquery')
module.exports = {
  onAttach: function () {
    $('body').bind('click', function (e) {
      $('.tech__popover').popover('hide')
    })
    this.$('.tech__popover').each(function (idx, ele) {
      var $ele = $(ele)
      var $content = $ele.next('.tech__item-content')
      $ele
      .on('click', function (e) {
        e.preventDefault()
        e.stopPropagation()
      })
      .popover({
        content: $('.tech__item-content--body', $content).html(),
        title: $('.tech__item-content--title', $content).text(),
        container: $ele.parents('.tech__item'),
        placement: 'top',
        html: true
      })
    })
  }
}
