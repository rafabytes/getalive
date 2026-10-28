/* Painel: arrastar as linhas da lista de templates muda a ordem no site. */
(function ($) {
  $(function () {
    var $list = $('#the-list');
    if (!$list.length || !window.gaOrder) return;

    $list.sortable({
      handle: '.ga-order__handle',
      axis: 'y',
      items: '> tr',
      helper: function (e, $row) {
        // Mantém a largura das células enquanto a linha é arrastada.
        $row.children().each(function () { $(this).width($(this).width()); });
        return $row;
      },
      update: function () {
        var ids = $list.children('tr').map(function () { return this.id.replace('post-', ''); }).get();
        $list.addClass('ga-order--saving');
        $.post(gaOrder.ajax, { action: 'ga_template_order', _ajax_nonce: gaOrder.nonce, offset: gaOrder.offset, ids: ids })
          .fail(function () {
            $list.sortable('cancel');
            window.alert('Não foi possível salvar a nova ordem. Recarregue a página e tente de novo.');
          })
          .always(function () { $list.removeClass('ga-order--saving'); });
      },
    });
  });
})(jQuery);
