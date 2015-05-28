var JIVOO = (function(parent, $, Cookies) {
  var my = parent.devbar = parent.devbar || {};
  
  var tools = [];
  
  var toolQueue = [];
  
  var $devbar = null;
  var $tools = null;
  var $more = null;
  var $moreMenu = null;
  
  var minimumWidth = null;

  var toolZIndex = 0;
  
  var stop = false;
  $(window).scroll(function() {
    stop = true;
  });

  var confGet = function(key, defaultValue) {
    var val = Cookies.get('jivoo-devbar-' + key);
    if (val === undefined)
      return defaultValue;
    return val;
  };
  
  var confSet = function(key, value) {
    Cookies.set('jivoo-devbar-' + key, value, { expires: 365, path: '/' });
  }
  
  function Tool(id, name, createContent) {
    this.id = id;
    this.name = name;
    this.createContent = createContent;
    this.$frame = null;
    this.$menuItem = null;
  }
  Tool.prototype.confGet = function(key, defaultValue) {
    return confGet(this.id + '-' + key, defaultValue);
  };
  Tool.prototype.confSet = function(key, value) {
    return confSet(this.id + '-' + key, value);
  };
  Tool.prototype.loadState = function() {
    if (!this.$frame)
      throw 'Frame not initialized!';
    var x = this.confGet('x', 0);
    var y = this.confGet('y', 0);
    var width = this.confGet('width', this.$frame.width());
    width = Math.max(width, 100);
    width = Math.min(width, $(window).width());
    this.$frame.width(width);
    var height = this.confGet('height', this.$frame.height());
    height = Math.max(height, 100);
    height = Math.min(height, $(window).height());
    this.$frame.height(height);
    x = Math.max(x, 0);
    y = Math.max(y, 0);
    x = Math.min(x, $(window).width() - this.$frame.width());
    y = Math.min(y, $(window).height() - this.$frame.height());
    this.$frame.css({top: y, left: x});
  };
  Tool.prototype.saveState = function() {
    if (!this.$frame)
      throw 'Frame not initialized!';
    var hidden = !this.$frame.is(':visible');
    this.confSet('x', parseInt(this.$frame.css('left')));
    this.confSet('y', parseInt(this.$frame.css('top')));
    this.confSet('width', this.$frame.outerWidth());
    this.confSet('height', this.$frame.outerHeight());
    this.confSet('open', this.$frame.is(':visible'));
  };
  Tool.prototype.createMenuItem = function() {
    this.$menuItem = $('<li>');
    var $link = $('<a href="#">');
    $link.text(this.name);
    this.$menuItem.append($link);
    var tool = this;
    this.$menuItem.click(function() {
      tool.toggle();
      return false;
    });
    if (this.confGet('open', 'false') !== 'false') {
      this.open();
    }
    return this.$menuItem;
  };
  Tool.prototype.createFrame = function() {
    var $frame = $('<div class="jivoo-frame">');
    var $title = $('<div class="jivoo-frame-title">');
    var $close = $('<div class="jivoo-frame-close">X</div>');
    var $content = $('<div class="jivoo-frame-content">');
    var showFrame = this.createContent($content, $frame);
    if (showFrame === false) {
      this.confSet('open', 'false');
      return false;
    }
    this.$frame = $frame;
    this.loadState();
    this.saveState();
    $title.html(this.name);
    $frame.append($close).append($title).append($content);
    var tool = this;
    $frame.draggable({
      scroll: false,
      containment: 'window',
      snap: true,
      snapMode: 'outer',
      handle: '.jivoo-frame-title',
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
        tool.saveState();
      }
    });
    $frame.css('position', 'fixed');
    $frame.mousedown(function(event) {
      tool.toTop();
    });
    $frame.resizable({
      containment: 'document',
      handles: 'all',
      stop: function(event, ui) {
        tool.saveState();
      }
    });
    $frame.hide();
    $close.click(function() {
      tool.close();
    });
    $(window).resize(function() {
      if ($frame.width() > $(window).width())
        $frame.width($(window).width());
      var pos = $frame.position();
      var x = Math.min(pos.left, $(window).width() - $frame.width());
      var y = Math.min(pos.top, $(window).height() - $frame.height());
      $frame.css({top: y, left: x});
      saveState();
    });
    $('body').append($frame);
    return true;
  };
  Tool.prototype.open = function() {
    if (!this.$frame && !this.createFrame())
      return;
    this.$frame.show();
    this.toTop();
    this.saveState();
  };
  Tool.prototype.close = function() {
    if (!this.$frame)
      return;
    this.$frame.hide();
    this.saveState();
  };
  Tool.prototype.toggle = function() {
    if (!this.$frame && !this.createFrame())
      return;
    this.$frame.toggle();
    this.toTop();
    this.saveState();
  };
  Tool.prototype.toTop = function() {
    if (!this.$frame)
      return;
    this.$frame.css('zIndex', toolZIndex++);
  };
  
  var rearrangeTools = function() {
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
  
  var updateTools = function() {
    if (!$devbar)
      return;
    while (toolQueue.length > 0) {
      var tool = toolQueue.shift();
      var $menuItem = tool.createMenuItem();
      $tools.append($menuItem);
      tools.push(tool);
    }
    rearrangeTools();
  };
  
  my.addTool = function(id, name, contentFunction) {
    var tool = new Tool(id, name, contentFunction);
    toolQueue.push(tool);
    updateTools();
    return tool;
  };
  
  my.addAjaxTool = function(id, name, url) {
    return this.addTool(id, name, function($content) {
      $content.html('loading...');
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
          $content.html(data)
        },
        error: function(xhr, status, error) {
          $content.text(status + ': ' + error);
        }
      });
      return true;
    });
  };
  
  my.addLinkTool = function(id, name, url) {
    return this.addTool(id, name, function($content) {
      location.href = url;
      return false;
    });
  };
  
  var loadState = function() {
    if (!$devbar)
      throw 'Jivoo Devbar not initialized!';
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
  };
  
  var saveState = function() {
    if (!$devbar)
      throw 'Jivoo Devbar not initialized!';
    confSet('x', $devbar.position().left);
    confSet('y', $devbar.position().top);
    confSet('width', $devbar.width());
    rearrangeTools();
  };
  
  my.toTop = function() {
    var zmax = 0;
    $('*').each(function() {
      var cur = parseInt($(this).css('z-index'));
      zmax = cur > zmax ? cur : zmax;
    });
    $devbar.css('z-index', zmax + 1);
    toolZIndex = zmax + 2;
  };
  
  my.showMoreMenu = function() {
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
  };

  my.hideMoreMenu = function() {
    $moreMenu.hide();
  };
  
  $(function() {
    $devbar = $('#jivoo-devbar');
    $tools = $devbar.children('.jivoo-devbar-tools');
    $more = $devbar.children('.jivoo-devbar-more');
    $more.hide();
    $moreMenu = $devbar.children('.jivoo-devbar-tools-more');
    $moreMenu.hide();

    var rightHandleWidth = $more.outerWidth() + $devbar.children('.ui-resizable-handle').outerWidth();
    minimumWidth = $devbar.children('.jivoo-devbar-handle').outerWidth() + rightHandleWidth;
    
    my.toTop();
    loadState();
    saveState();

    updateTools();

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
      var pos = $devbar.position();
      var x = Math.min(pos.left, $(window).width() - $devbar.width());
      var y = Math.min(pos.top, $(window).height() - $devbar.height());
      $devbar.css({top: y, left: x});
      saveState();
    });
    $devbar.resizable({
      handles: { e: '.ui-resizable-handle' },
      containment: 'document',
      minWidth: minimumWidth
    });
    
    $more.click(my.showMoreMenu);
  });
  
  return parent;
}(JIVOO || {}, jQuery, Cookies))

$(function() {
  var logTool = JIVOO.devbar.addTool('jivoo-log', 'Log', function ($content) {
    var $log = $('<div class="jivoo-console-log">');
    jivooLog.forEach(function(entry) {
      var $entry = $('<div class="jivoo-console-log-entry"></div>');
      var message = entry.message;
      if (entry.file) {
        message += ' in <em>' + entry.file + '</em> on line <strong>' + entry.line + '</strong>';
      }
      $entry.html(message);
      switch (entry.type) {
      case 1: // QUERY
        console.debug(message);
        $entry.css('color', '#999');
        break;
      case 2: // DEBUG
        console.debug(message);
        $entry.css('color', '#99f');
        break;
      case 4: // NOTICE
        console.log(message);
        $entry.css('color', '#aa2');
        break;
      case 8: // WARNING
        console.warn(message);
        $entry.css('color', '#f90');
        break;
      case 16: // ERROR
        console.error(message);
        $entry.css('color', '#f00');
        break;
      }
      $log.append($entry);
    });
    $content.append($log);
  });
  
  
  logTool.$menuItem.children('a').append($('<span class="jivoo-devbar-count">').text(jivooLog.length));
});
