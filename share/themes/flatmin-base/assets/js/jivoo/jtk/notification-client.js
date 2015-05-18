$(function() {
  $('.jtk-notifications').each(function() {
    var $container = $(this);
    var url = $container.data('server');
    var poller = function() {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response !== undefined) {
            for (var i = 0; i < response.length; i++) {
              var $notification = $('<div class="jtk-notification">');
              $notification.text(response[i].message);
              $container.append($notification);
            }
          }
          var interval = setInterval(function() {
            clearInterval(interval);
            poller();
          }, 1000);
        },
        error: function(jqXHR, textStatus, errorThrown) {
        }
      });
    };
    poller();
  });
});