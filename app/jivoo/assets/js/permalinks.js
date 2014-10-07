$(function() {
  $('input[type=text][data-auto-permalink]').keyup(function() {
    var target = $(this).data('auto-permalink');
    $('#' + target).val(
      $(this).val().toLowerCase().replace(/[^a-z0-9 ]/g, '').replace(' ', '-')
    );
  });
});