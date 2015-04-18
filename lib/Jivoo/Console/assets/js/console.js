$(function() {
  var $devbar = $('#jivoo-devbar');
  var $tools = $devbar.children('.jivoo-devbar-tools');
  $tools.sortable({
    cursor: 'move',
    scroll: false,
    appendTo: 'body',
  });
  var $more = $devbar.children('.jivoo-devbar-more');
  var rightHandleWidth = $more.outerWidth() + $devbar.children('.ui-resizable-handle').outerWidth();
  var minimumWidth = $devbar.children('.jivoo-devbar-handle').outerWidth() + rightHandleWidth;
  $more.hide();
  var confGet = function(key, defaultValue) {
    var val = Cookies.get('jivoo-devbar-' + key);
    if (val === undefined)
      return defaultValue;
    return val;
  };
  var x = confGet('x', 0);
  var y = confGet('y', 0);
  var width = confGet('width', $devbar.width());
  width = Math.max(width, minimumWidth);
  width = Math.min(width, $(window).width());
  $devbar.width(width);
  x = Math.max(x, 0);
  y = Math.max(y, 0);
  x = Math.min(x, $(window).width() - $devbar.width());
  y = Math.min(y, $(window).height() - $devbar.height());
  $devbar.css({top: y, left: x});
  var saveState = function() {
    Cookies.set('jivoo-devbar-x', $devbar.position().left);
    Cookies.set('jivoo-devbar-y', $devbar.position().top);
    Cookies.set('jivoo-devbar-width', $devbar.width());
    $more.hide();
    $tools.children().each(function() {
      var x2 = $(this).position().left + $(this).width();
      if (x2 > $devbar.width()) {
        $(this).css('visibility', 'hidden');
        $more.show();
      }
      else {
        $(this).css('visibility', 'visible');
      }
    });
  };
  saveState();
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
      saveState();
    }
  });
  $(window).resize(function() {
    if ($devbar.width() > $(window).width())
      $devbar.width($(window).width());
    pos = $devbar.position();
    x = Math.min(pos.left, $(window).width() - $devbar.width());
    y = Math.min(pos.top, $(window).height() - $devbar.height());
    $devbar.css({top: y, left: x});
    saveState();
  });
  $devbar.resizable({
    handles: { e: '.ui-resizable-handle' },
    containment: 'document',
    minWidth: minimumWidth
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