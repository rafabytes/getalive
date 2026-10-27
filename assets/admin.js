/* Painel: cenas do template (repetidor com imagem) e seletor de cor. */
(function ($) {
  $(function () {
    $('.ga-color').wpColorPicker();

    var $list = $('.ga-scenes');
    if (!$list.length) return;

    $list.sortable({ handle: '.ga-scene__handle', axis: 'y' });

    $('.ga-scene-add').on('click', function () {
      var i = parseInt($list.attr('data-next'), 10) || 0;
      var html = $('#tmpl-ga-scene').html().replace(/__i__/g, i);
      $list.attr('data-next', i + 1).append(html);
      $list.children().last().find('input[type=text]').trigger('focus');
    });

    $list.on('click', '.ga-scene__remove', function () {
      $(this).closest('.ga-scene').remove();
    });

    $list.on('click', '.ga-scene__image', function (e) {
      e.preventDefault();
      var $row = $(this).closest('.ga-scene');
      var frame = wp.media({ title: 'Imagem da cena', library: { type: 'image' }, multiple: false, button: { text: 'Usar imagem' } });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        var url = (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail : att).url;
        $row.find('.ga-scene__image-id').val(att.id);
        $row.find('.ga-scene__image').html('<img src="' + url + '" alt="">');
      });
      frame.open();
    });
  });
})(jQuery);
