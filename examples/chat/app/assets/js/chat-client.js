$(function() {
  $('#chat-log').each(function() {
    var $log = $(this);
    var url = $log.data('server');
    var poller = function() {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response !== undefined) {
            var lastMessage = 0;
            for (var i = 0; i < response.length; i++) {
              lastMessage = response[i].id;
              $log.append('<div class="message">' + response[i].message + '</div>')
            }
            url = $log.data('server') + '?lastMessage=' + lastMessage;
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
  
  $('#Message').submit(function() {
    $('#Message input').prop('disabled', true);
    var data = {
      access_token: $('input[name=access_token]').val(),
      Message: {
        message: $('#Message_message').val()
      }
    };
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      dataType: 'json',
      data: data,
      success: function(response) {
        $('#Message input').prop('disabled', false);
        if (response === 'success') {
          $('#Message_message').val('');
        }
      }
    })
    return false;
  });
});