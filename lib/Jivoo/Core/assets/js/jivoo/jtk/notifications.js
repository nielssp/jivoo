var JIVOO = (function(parent) {
  var my = parent.notifications = parent.notifications || {};


  var listeners = [];

  my.receive = function(handler) {
    listeners.push(handler);
  };


  my.send = function(message) {
    var notification = {
      message: message
    };
    for (var i = 0; i < listeners.length; i++) {
      listeners[i](notification);
    }
  };

  return parent;
})(JIVOO || {});
