var $ = require('jquery')
module.exports = {
  onAttach: function () {
    this.$('.tech__popover').each(function (idx, ele) {
      var $ele = $(ele)
      var $content = $ele.next('.tech__item-content')
      $ele
      .on('click', function (e) {
        e.preventDefault()
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
