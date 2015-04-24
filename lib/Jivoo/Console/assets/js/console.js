var jivooDevbar = {
  tools: [],
  addTool: function(id, name, contentFunction) {
    this.tools.push({
      name: name,
      id: id,
      createContent: contentFunction
    });
  },
  addAjaxTool: function(id, name, url) {
    this.addTool(id, name, function() {
      $.ajax({
        
      });
      return true;
    });
  },
  addLinkTool: function(id, name, url) {
    this.addTool(id, name, function() {
      location.href = url;
      return false;
    });
  }
};

$(function() {
  $.fn.maxZIndex = function() {
    var zmax = 0;
    $('*').each(function() {
      var cur = parseInt($(this).css('z-index'));
      zmax = cur > zmax ? cur : zmax;
    });
    return this.each(function() {
      zmax += 10;
      $(this).css("z-index", zmax);
    });
  }
  
  var $devbar = $('#jivoo-devbar');
  $devbar.maxZIndex();
  var $tools = $devbar.children('.jivoo-devbar-tools');
  var $more = $devbar.children('.jivoo-devbar-more');
  var $moreMenu = $devbar.children('.jivoo-devbar-tools-more');
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
    Cookies.set('jivoo-devbar-x', $devbar.position().left, { expires: 365, path: '/' });
    Cookies.set('jivoo-devbar-y', $devbar.position().top, { expires: 365, path: '/' });
    Cookies.set('jivoo-devbar-width', $devbar.width(), { expires: 365, path: '/' });
    $more.hide();
    $moreMenu.children().remove();
    $tools.children().each(function() {
      var x2 = $(this).position().left + $(this).width();
      if (x2 > $devbar.width()) {
        $(this).css('visibility', 'hidden');
        $more.show();
        $(this).clone(true).css('visibility', 'visible').prependTo($moreMenu);
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
  $moreMenu.hide();
  $more.click(function() {
    var xf = ($more.offset().left - $(window).scrollLeft()) / $(window).width();
    var yf = ($more.offset().top - $(window).scrollTop()) / $(window).height();
    $moreMenu.css({top: '', left: '', right: '', bottom: ''});
    if (xf < 0.5) {
      if (yf < 0.5)
        $moreMenu.css({top: '0', left: '100%'}); // top left
      else
        $moreMenu.css({bottom: '0', left: '100%'}); // bottom left
    }
    else {
      if (yf < 0.5)
        $moreMenu.css({top: '100%', right: '0'}); // top right
      else
        $moreMenu.css({bottom: '100%', right: '0'}); // bottom right
    }
    $moreMenu.show();
    var buttonPos = $more.position();
    $(document).one('click',function(){
        $moreMenu.hide(); 
     });
    return false;
  });
  
  
  $('.jivoo-devbar-log-count').text(jivooLog.length);
  
  $tools.children().children().each(function() {
    var $frame = $('<div class="jivoo-frame">')
    var $title = $('<div class="jivoo-frame-title">');
    var $close = $('<div class="jivoo-frame-close">X</div>');
    var $content = $('<div class="jivoo-frame-content">');
    $title.html($(this).html());
    $frame.append($close).append($title).append($content);
    $frame.draggable({
      scroll: false,
      containment: 'window',
      snap: true,
      snapMode: 'outer',
      handle: '.jivoo-frame-title'
    });
    $frame.mousedown(function(event) {
      //$frame.maxZIndex();
      if (event.altKey) {
        $frame.trigger(event);
      }
    });
    $frame.resizable({
      containment: 'document',
      handles: 'all'
    });
    $frame.hide();
    $close.click(function() {
      $frame.hide();
    });
    $('body').append($frame);
    $(this).click(function() {
      $frame.toggle();
    }); 
  });
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