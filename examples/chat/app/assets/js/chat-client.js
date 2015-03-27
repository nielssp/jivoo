$(function() {
  $('#chat-log').each(function() {
    var log = this;
    var $log = $(log);
    var url = $log.data('server');
    var poller = function() {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response !== undefined) {
            var currentScroll = $log.scrollTop();
            var scrollHeight = log.scrollHeight - $log.innerHeight(); 
            var lastMessage = 0;
            for (var i = 0; i < response.length; i++) {
              lastMessage = response[i].id;
              var $message = $('<div class="message">');
              var $author = $('<span class="message-author">');
              if (response[i].author === null) {
                $author.text('anonymous');
                $message.addClass('message-anonymous');
              }
              else {
                var name = response[i].author;
                var hash = 0;
                for (var j = 0, len = name.length; j < len; j++)
                  hash += name.charCodeAt(j);
                var hue = hash % 360;
                $author.css('color', 'hsl(' + hue + ', 60%, 50%)');
                $author.text(response[i].author);
              }
              var $text = $('<span class="message-text">');
              $text.text(response[i].message);
              $message.append($author).append($text);
              $log.append($message);
            }
            url = $log.data('server') + '?lastMessage=' + lastMessage;
            if (currentScroll >= scrollHeight)
              $log.scrollTop(log.scrollHeight - $log.innerHeight());
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
  
  $('#change-name').click(function() {
    $('#Message_message').focus().val('/name ');
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
        $('#Message_message').focus()
        if (response === 'success') {
          $('#Message_message').val('');
        }
      }
    })
    return false;
  });
  
  $('#Message_message').focus();
});