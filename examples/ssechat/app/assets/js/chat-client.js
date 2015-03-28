$(function() {
  $('#chat-log').each(function() {
    var log = this;
    var $log = $(log);
    var url = $log.data('server');

    var addMessage = function(data) {
      var currentScroll = $log.scrollTop();
      var scrollHeight = log.scrollHeight - $log.innerHeight(); 
      var $message = $('<div class="message">');
      var $author = $('<span class="message-author">');
      if (data.author === null) {
        $author.text('anonymous');
        $message.addClass('message-anonymous');
      }
      else {
        var name = data.author;
        var hash = 0;
        for (var j = 0, len = name.length; j < len; j++)
          hash += name.charCodeAt(j);
        var hue = hash % 360;
        $author.css('color', 'hsl(' + hue + ', 60%, 50%)');
        $author.text(data.author);
      }
      var $text = $('<span class="message-text">');
      $text.text(data.message);
      $message.append($author).append($text);
      $log.append($message);
      if (currentScroll >= scrollHeight)
        $log.scrollTop(log.scrollHeight - $log.innerHeight());
    };

    if (!window.EventSource)
      throw 'EventSource not supported by browser.';

    var source = new EventSource(url);
    source.addEventListener('message', function(e) {
      var data = JSON.parse(e.data);
      addMessage(data);
    });
    source.addEventListener('open', function(e) {
      console.log('Connection opened');
    });
    source.addEventListener('error', function(e) {
      console.log('Error');
      console.log(e);
      if (e.readyState == EventSource.CLOSED) {
        console.log('Connection closed');
      }
    });
  };
  
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
