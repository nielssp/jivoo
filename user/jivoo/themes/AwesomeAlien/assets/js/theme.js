$(function() {
  $('.comment').each(function() {
    var $byline = $(this).find('.byline');
    $('#comment-form input[name="cancel"]').click(function() {
      $('#comment-form').appendTo($('#comment-form-container'));
      return false;
    });
    $(this).find('.reply').click(function() {
      $('#comment-form').insertAfter($byline);
      return false;
    });
  });
});