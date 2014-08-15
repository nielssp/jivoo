$(function() {
  $('.toggle-menu').click(function() {
    $('body').toggleClass('menu-open');
    
  });
  $('nav > ul > li > a').click(function() {
    $('nav > ul > li > a').removeClass('current');
    $(this).addClass('current');
  });
  $('.dropdown-actions button').prop('disabled', true);
  $('table').each(function() {
    var $table = $(this);
    var itemCount = $('.item-count').html();
    var updateCounter = function() {
      var selection = $table.find('td input[type=checkbox]:checked').length;
      var max = $table.find('td input[type=checkbox]').length;
      $('.dropdown-actions button').prop('disabled', selection == 0);
      if (selection == max)
        $table.find('th input[type=checkbox]').prop('checked', true);
      else
        $table.find('th input[type=checkbox]').prop('checked', false);
      $('.table-operations .selection-count').html(selection);
      if (selection == max && selection != itemCount) {
        $('.table-operations .select-all').show().click(function() {
          $('.table-operations .selection-count').html(itemCount);
          $('.table-operations .select-all').hide();
          return false;
        });
      }
      else {
        $('.table-operations .select-all').hide();
      }
    };
    $table.find('td input[type=checkbox]').change(function() {
      updateCounter();
    });
    $table.find('th input[type=checkbox]').change(function() {
      if ($(this).is(':checked')) {
        $table.find('input[type=checkbox]').prop('checked', true);
      }
      else {
        $table.find('input[type=checkbox]').prop('checked', false);
      }
      updateCounter();
    });
  });
});
