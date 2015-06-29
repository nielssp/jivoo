var JIVOO = (function(parent) {
  var my = parent.notifications = parent.notifications || {};


  var listeners = [];
  

  my.onReceive = function(handler) {
    listeners.push(handler);
  };
  
  my.startLoading = function() {};
  
  my.stopLoading = function() {};

  my.send = function(message, type) {
    type = type || '';
    var notification = {
      type: type,
      message: message
    };
    for (var i = 0; i < listeners.length; i++) {
      listeners[i](notification);
    }
  };
  
  // TODO: move..? also depends on jquery
  parent.ajax = function(settings) {
    my.startLoading();
    settings.complete = my.stopLoading;
    var success = settings.success || function() {};
    settings.success = [settings.success, function(data) {
      if (data.notifications) {
        for (var i = 0; i < data.notifications.length; i++)
          my.send(data.notifications[i].message, data.notifications[i].type);
      }
    }];
    settings.error = function(xhr, status, error) {
      my.send(error, 'error');
    };
    $.ajax(settings);
  };

  return parent;
})(JIVOO || {});
