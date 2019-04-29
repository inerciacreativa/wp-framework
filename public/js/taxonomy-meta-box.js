/**
 * @param {jQuery} $ jQuery object.
 */
(function ($) {
  $('.ic-meta-box').each(function () {
    var div = $(this),
      id = div.attr('id'),
      parts,
      taxonomy,
      terms;

    parts = id.split('-');
    parts.shift();
    taxonomy = parts.join('-');
    terms = $('#' + taxonomy + '-all, #' + taxonomy + '-pop').find('input');

    // Fix to create non hierarchical terms when multiple terms are allowed.
    // Concatenates the values of the 'data-value' attributes of the inputs.
    /*
  if (!div.hasClass('ic-meta-box-hierarchical')) {
    $('#post').submit(function () {
      var values = [],
        input = $('input[name="tax_input[' + taxonomy + ']"]');

      $('#' + taxonomy + '-all :checked').each(function () {
        values.push($(this).attr('data-value'));
      });

      input.val(values.join(','));

      return true;
    });
  }
  */

    // Fix to sync popular terms.
    terms.on('click', function () {
      var input = $(this),
        id = input.val(),
        checked = input.is(':checked');

      terms.filter(':radio').prop('checked', false);
      terms.filter('[value="' + id + '"]').prop('checked', checked);
    });
  });
}(jQuery));
