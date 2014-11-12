$(function() {
  $('.comments li').each(function() {
    var id = $(this).attr('id').replace('comment', '');
    var $byline = $(this).find('.byline');
    $('#comment input[name="cancel"]').click(function() {
      $('#comment').appendTo($('#comment-form-container'));
      $('#comment input[name="Comment[parentId]"]').val(null);
      return false;
    });
    $(this).find('.reply').click(function() {
      $('#comment').insertAfter($byline);
      $('#comment input[name="Comment[parentId]"]').val(id);
      return false;
    });
  });
});