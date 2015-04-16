$(function() {
  var $devbar = $('#jivoo-devbar');
  var x = Cookies.get('jivoo-devbar-x');
  if (x === undefined)
    x = 0;
  var y = Cookies.get('jivoo-devbar-y');
  if (y === undefined)
    y = 0;
  x = Math.max(x, 0);
  y = Math.max(y, 0);
  x = Math.min(x, $(window).width() - $devbar.width());
  y = Math.min(y, $(window).height() - $devbar.height());
  $devbar.css({top: y, left: x});
  var savePosition = function() {
    Cookies.set('jivoo-devbar-x', $devbar.position().left);
    Cookies.set('jivoo-devbar-y', $devbar.position().top);
  };
  savePosition();
  var stop = false;
  $(window).scroll(function() {
    stop = true;
  });
  $devbar.draggable({
    scroll: false,
    containment: 'window',
    handle: '.jivoo-devbar-handle',
    start: function(event, ui) {
      stop = false;
    },
    drag: function(event, ui) {
      if (stop) {
        stop = false;
        return false;
      }
    },
    stop: function(event, ui) {
      savePosition();
    }
  });
  $(window).resize(function() {
    pos = $devbar.position();
    x = Math.min(pos.left, $(window).width() - $devbar.width());
    y = Math.min(pos.top, $(window).height() - $devbar.height());
    console.log(x);
    $devbar.css({top: y, left: x});
    savePosition();
  });
  $('#jivoo-devbar-log-count').text(jivooLog.length);
//  var $log = $('#jivoo-console-log');
//  $log.resizable({
//    handles: 'n'
//  });
//  $log.hide();
//  jivooLog.forEach(function(entry) {
//    var $entry = $('<div class="jivoo-console-log-entry"></div>');
//    var message = entry.message;
//    if (entry.file) {
//      message += ' in <em>' + entry.file + '</em> on line <strong>' + entry.line + '</strong>';
//    }
//    $entry.html(message);
//    switch (entry.type) {
//    case 1: // QUERY
//      console.debug(message);
//      $entry.css('color', '#999');
//      break;
//    case 2: // DEBUG
//      console.debug(message);
//      $entry.css('color', '#99f');
//      break;
//    case 2: // NOTICE
//      console.log(message);
//      $entry.css('color', '#f00');
//      break;
//    case 4: // WARNING
//      console.warn(message);
//      $entry.css('color', '#f90');
//      break;
//    case 8: // ERROR
//      console.error(message);
//      $entry.css('color', '#f00');
//      break;
//    }
//    $log.append($entry);
//  });
});