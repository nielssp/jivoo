$(function() {
  var $devbar = $('#jivoo-devbar');
  var x = Cookies.get('jivoo-devbar-x');
  if (x === undefined)
    x = 0;
  var y = Cookies.get('jivoo-devbar-y');
  if (y === undefined)
    y = 0;
  console.log(x + "x" + y);
  x = Math.max(x, 0);
  y = Math.max(y, 0);
  x = Math.min(x, $(window).width() - $devbar.width());
  y = Math.min(y, $(window).height() - $devbar.height());
  console.log(x + "x" + y);
  $devbar.css({top: y, left: x});
  $devbar.draggable({
    scroll: false,
    handle: '.jivoo-devbar-handle',
    stop: function(event, ui) {
      var y = ui.helper.offset().top - $(window).scrollTop();
      var x = ui.helper.offset().left - $(window).scrollLeft();
      ui.helper.css({position: 'fixed', top: y, left: x});
      Cookies.set('jivoo-devbar-x', $(this).position().left);
      Cookies.set('jivoo-devbar-y', $(this).position().top);
    }
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