var $ = require('jquery')
module.exports = {
  onAttach: function () {
    this.$('.tech__popover--trigger').each(function (idx, ele) {
      var $ele = $(ele)
      var $content = $ele.next('.tech__popover')
      $ele
      .on('click', function (e) {
        e.preventDefault()
      })
      .popover({
        content: $content.find('.tech__popover--body').html(),
        title: $content.find('.tech__popover--title').text(),
        container: $ele.parents('li'),
        placement: 'top',
        html: true
      })
    })
  }
}
