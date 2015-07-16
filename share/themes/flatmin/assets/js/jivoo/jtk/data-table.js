$(function() {
  
  $.amaran({
    content: {
      themeName: 'notification selection',
      message: ''
    },
    sticky: true,
    themeTemplate: JIVOO.notifications.themeTemplate(true),
    position: 'top right',
    inEffect: 'slideTop',
    closeOnClick: false
  });
  
  var $selection = $('.notification.selection');
  $selection.find('.message').html($('.table-selection').html());
  $selection.hide();
  
  $('.data-table').each(function() {
    var $dataTable = $(this);
    var allSelected = false;
    var $table = $dataTable.find('table');
    var itemCount = $('.item-count').html();
    var updateCounter = function() {
      allSelected = false;
      var selection = $table.find('td input[type=checkbox]:checked').length;
      var max = $table.find('td input[type=checkbox]').length;
      $('.dropdown-actions button').prop('disabled', selection == 0);
      if (selection == max)
        $table.find('th input[type=checkbox]').prop('checked', true);
      else
        $table.find('th input[type=checkbox]').prop('checked', false);
      $selection.find('.selection-count').html(selection);
      if (selection > 0) {
        $selection.fadeIn(100);
      }
      else {
        $selection.fadeOut(100);
      }
      if (selection == max && selection != itemCount) {
        $selection.find('.select-all').show().click(function() {
          $selection.find('.selection-count').html(itemCount);
          $selection.find('.select-all').hide();
          allSelected = true;
          return false;
        });
      }
      else {
        $selection.find('.select-all').hide();
      }
    };
    updateCounter();
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


    // BULK ACTIONS
    $dataTable.find('.dropdown-actions button').click(function() {
      var $button = $(this);
      var method = $button.data('method');
      var action = $button.data('action');
      if (allSelected) {
        var filter = $dataTable.find('#filter_filter').val();
        var action = action.replace('%3F', '') + '?filter=' + filter;
      }
      else {
        var ids = $table.find('td input[type=checkbox]:checked').map(function() {
          return $(this).val();
        }).get().join();
        action = action.replace('%3F', ids);
      }
      if (method == 'get') {
        location.href = action;
      }
      else {
        var data = $.extend(true, {
          access_token: $('input[name=access_token]').val()
          }, $button.data('data'));
        var confirmation = $button.data('confirm');
        if (confirmation !== undefined && !confirm(confirmation)) {
          return false;
        }
        JIVOO.ajax({
          url: action,
          type: 'POST',
          data: data,
          success: function() {
            location.reload();
            // TODO: reload using AJAX
          }
        });
      }
    });
    

    // ROW ACTIONS
    $table.find('td.actions a').click(function(e) {
      var $link = $(this);
      var method = $link.data('method');
      var action = $link.attr('href');
      var confirmation = $link.data('confirm');
      if (confirmation !== undefined && !confirm(confirmation)) {
        return false;
      }
      if (method != 'get') {
        var data = $.extend(true, {
          access_token: $('input[name=access_token]').val()
          }, $link.data('data'));
        JIVOO.ajax({
          url: action,
          type: 'POST',
          data: data,
          success: function() {
            location.reload();
            // TODO: reload using AJAX
          }
        });
        return false;
      }
    });
    
    
    // TABLE SETTINGS
    var $tableSettings = $dataTable.find('.table-settings-box');
    $dataTable.find('.table-settings').click(function() {
      var position = $(this).offset();
      $tableSettings.css('right', $(document).width() - position.left - $(this).width());
      $tableSettings.css('top', position.top + $(this).height());
      $tableSettings.toggle(200);
      return false;
    });
    
    
    // FIND
    $dataTable.find('.table-find').click(function() {
      alert('find');
    });
  });
});