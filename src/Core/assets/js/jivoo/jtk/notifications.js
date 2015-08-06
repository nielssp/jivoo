var JTK = (function(parent) {
  var my = parent.notifications = parent.notifications || {};


  var listeners = [];
  
  var counter = 0;

  my.onReceive = function(handler) {
    listeners.push(handler);
  };
  
  my.startLoading = function() {};
  
  my.stopLoading = function() {};

  my.send = function(message, type) {
    type = type || '';
    var notification = {
      id: counter++,
      type: type,
      message: message
    };
    for (var i = 0; i < listeners.length; i++) {
      listeners[i](notification);
    }
  };
  
  var clientOnline = false;
  var clientInterval = null;
  
  my.startClient = function(server, delay) {
    if (clientOnline)
      my.stopClient();
    delay = delay || 1000;
    clientOnline = true;
    var poll = function() {
      $.ajax({
        url: server,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response !== undefined) {
            for (var i = 0; i < response.length; i++)
              my.send(response[i]);
          }
          clientInterval = setInterval(function() {
            if (!clientOnline)
              return;
            clearInterval(clientInterval);
            poller();
          }, delay);
        },
        error: function(xhr, status, error) {
          my.send(errorThrown, 'error');
        }
      });
    };
    poll();
  };
  
  my.stopClient = function() {
    clientOnline = false;
    clearInterval(clientInterval);
  };
  
  parent.notify = my.send;
  
  // TODO: move..? also depends on jquery
  parent.ajax = function(settings) {
    if (settings.showLoading !== false) {
      my.startLoading();
      var complete = settings.complete || function() {};
      settings.complete = [complete, my.stopLoading];
    }
    var success = settings.success || function() {};
    settings.success = [success, function(data) {
      if (data.notifications) {
        for (var i = 0; i < data.notifications.length; i++)
          my.send(data.notifications[i].message, data.notifications[i].type);
      }
    }];
    var error = settings.error || function() {};
    settings.error = [settings.error, function(xhr, status, error) {
      my.send(error, 'error');
    }];
    $.ajax(settings);
  };

  return parent;
})(JTK || {});
