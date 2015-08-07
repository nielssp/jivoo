var JIVOO = (function(parent, $, Cookies) {
  var my = parent.devbar = parent.devbar || {};
  
  var tools = [];
  
  var toolQueue = [];
  
  var $devtools =  null;
  var $devbar = null;
  var $tools = null;
  var $toolframe = null;
  var $toolframecont = null;
  
  var dock = 'bottom';
  
  var $fade = null;
  var $hide = null;
  
  var currentTool = null;
  
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
  
  function Tool(id, name, createContent, url, allowOpen) {
    this.id = id;
    this.name = name;
    this.createContent = createContent;
    this.url = url;
    this.allowOpen = allowOpen;
    this.$menuItem = null;
    this.$content = null;
  }
  Tool.prototype.confGet = function(key, defaultValue) {
    return confGet(this.id + '-' + key, defaultValue);
  };
  Tool.prototype.confSet = function(key, value) {
    return confSet(this.id + '-' + key, value);
  };
  Tool.prototype.loadState = function() {
  };
  Tool.prototype.saveState = function() {
    if (currentTool == this)
      this.confSet('open', 'true');
    else
      this.confSet('open', 'false');
  };
  Tool.prototype.createMenuItem = function() {
    this.$menuItem = $('<div>');
    var $link = $('<a>');
    if (this.url !== undefined)
      $link.attr('href', this.url);
    $link.text(this.name);
    this.$menuItem.append($link);
    var tool = this;
    this.$menuItem.click(function(e) {
      if (e.button == 0) {
        tool.toggle();
        return false;
      }
    });
    if (this.confGet('open', 'false') !== 'false') {
      if (!this.allowOpen)
        this.confSet('open', 'false');
      else
        this.open(false);
    }
    return this.$menuItem;
  };
  Tool.prototype.open = function(animate) {
    if (this.$content === null) {
      this.$content = $('<div>'); 
      var showFrame = this.createContent(this.$content);
      if (showFrame === false)
        return;
    }
    $toolframe.children('.jivoo-dev-frame-content').html(this.$content);
    if (currentTool !== null) {
      var prev = currentTool;
      currentTool = this;
      prev.saveState();
    }
    currentTool = this;
    this.saveState();
    if (animate) {
      $toolframe.show('blind', updateSize);
    }
    else {
      $toolframe.show();
      updateSize();
    }
  };
  Tool.prototype.close = function() {
    currentTool = null;
    $toolframe.hide('blind', updateSize);
    this.saveState();
  };
  Tool.prototype.toggle = function() {
    if (currentTool == this)
      this.close();
    else
      this.open(true);
  };
  Tool.prototype.toTop = function() {
    if (!this.$frame)
      return;
    this.$frame.css('zIndex', toolZIndex++);
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
  };
  
  my.addTool = function(id, name, contentFunction, url, allowOpen) {
    if (allowOpen === undefined) allowOpen = true;
    var tool = new Tool(id, name, contentFunction, url, allowOpen);
    toolQueue.push(tool);
    updateTools();
    return tool;
  };
  
  my.addAjaxTool = function(id, name, url, ajaxOnly) {
    var link = undefined;
    if (ajaxOnly !== true)
      link = url;
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
    }, link);
  };
  
  my.addLinkTool = function(id, name, url) {
    return this.addTool(id, name, function($content) {
      location.href = url;
      return false;
    }, url, false);
  };
  
  var loadState = function() {
    if (!$devbar)
      throw 'Jivoo Devbar not initialized!';
    
    if (confGet('hide', 'true') === 'true')
      $hide.prop('checked', true);
    if (confGet('fade', 'false') === 'true')
      $fade.prop('checked', true);

    if ($fade.is(':checked'))
      $devtools.css('opacity', 0.4);

    if ($hide.is(':checked'))
      $devtools.addClass('jivoo-dev-tools-minimize');
    
    dock = confGet('dock', dock);
    if (!$.inArray(dock, ['bottom', 'top']))
      dock = 'bottom';
  };
  
  var $lengthener = $('<div>');
  $lengthener.css({ position: 'absolute', width: '100%', zIndex: '-1' });
  
  var updateSize = function() {
    if (dock == 'bottom') {
      var height = $devtools.height();
      var previousHeight = $lengthener.height();
      $lengthener.css({ top: $(document).height() - previousHeight, height: height });
    }
    else {
      $lengthener.css({ top: 0, height: 0 });
    }
  };
  
  var saveState = function() {
    if (!$devbar)
      throw 'Jivoo Devbar not initialized!';
    if (dock == 'bottom' || dock == 'top')
      confSet('size', $toolframe.height());
    else
      confSet('size', $toolframe.width());
    
    confSet('hide', $hide.is(':checked') ? 'true' : 'false');
    confSet('fade', $fade.is(':checked') ? 'true' : 'false');
    
    confSet('dock', dock);
    updateSize();
  };
  
  my.toTop = function() {
    var zmax = 0;
    $('*').each(function() {
      var cur = parseInt($(this).css('z-index'));
      zmax = cur > zmax ? cur : zmax;
    });
    $devtools.css('z-index', zmax + 1);
  };
  
  var move = function(newDock) {
    if ($devtools.hasClass('jivoo-dev-tools-' + newDock))
      return;
    $devtools.removeClass('jivoo-dev-tools-left jivoo-dev-tools-right jivoo-dev-tools-top jivoo-dev-tools-bottom');
    
    var size = confGet('size', 150);
    size = Math.max(size, 50);
    size = Math.min(size, $(window).height() - 100);
    
    $toolframe.css({ width: '', height: '' });
    
    if (newDock == 'left' || newDock == 'top') {
      $toolframecont.prependTo($devtools);
    }
    else {
      $toolframecont.appendTo($devtools);
    }
    
    if (newDock == 'bottom' || newDock == 'top') {
      $toolframe.height(size);
    }
    else {
      $toolframe.width(size);
    }
    
    $devtools.addClass('jivoo-dev-tools-' + newDock);

    $toolframe.children('.ui-resizable-handle').hide();

    if (newDock == 'left') $toolframe.children('.ui-resizable-e').show();
    if (newDock == 'right') $toolframe.children('.ui-resizable-w').show();
    if (newDock == 'top') $toolframe.children('.ui-resizable-s').show();
    if (newDock == 'bottom') $toolframe.children('.ui-resizable-n').show();
    
    dock = newDock;
    saveState();
  }; 
  
  $(function() {
    $devtools = $('#jivoo-dev-tools');
    $toolframecont = $devtools.children('.jivoo-dev-frame-container');
    $toolframe = $toolframecont.children('.jivoo-dev-frame');
    $devbar = $devtools.children('.jivoo-devbar');
    $tools = $devbar.children('.jivoo-devbar-tools');

    $fade = $devbar.find('.jivoo-devbar-fade');
    $hide = $devbar.find('.jivoo-devbar-hide');
    
    $fade.click(saveState)
    $hide.click(function() {
      $toolframecont.hide('blind', function() {
        $devtools.addClass('jivoo-dev-tools-minimize');
        $hide.prop('checked', true);
        saveState();
      });
    });
    
    $devbar.children('.jivoo-devbar-handle').mousedown(function() {
      if ($devtools.is('.jivoo-dev-tools-minimize')) {
        setTimeout(function() {
          if ($devtools.is('.ui-draggable-dragging'))
            return;
          $toolframecont.show('blind', function() {
            $devtools.removeClass('jivoo-dev-tools-minimize');
            $hide.prop('checked', false);
            saveState();
          });
        }, 200);
      }
    });
    
    my.toTop();

    loadState();
    updateTools();
    
    $devtools.mouseover(function() {
      if ($fade.is(':checked'))
        $devtools.animate({ opacity: 1 }, { duration: 200, queue: false });
    });
    $devtools.mouseout(function() {
      if ($fade.is(':checked'))
        $devtools.animate({ opacity: 0.4 }, { duration: 200, queue: false });
    });

    $devtools.draggable({
      scroll: false,
      handle: '.jivoo-devbar-handle',
      start: function(event, ui) {
        stop = false;
      },
      drag: function(event, ui) {
        if (stop) {
          stop = false;
          return false;
        }
        var top = event.clientY / $(window).height();
        var left = event.clientX / $(window).width();
        if (top < 0.33)
          move('top');
        else if (top > 0.67)
          move('bottom');
//        else if (left < 0.5)
//          move('left');
//        else
//          move('right');
      },
      stop: function(event, ui) {
        $devtools.css({
          top: '',
          left: '',
          right: '',
          bottom: '',
          height: '',
          width: ''
        });
      }
    });
    
    $devbar.dblclick(function() {
      if (currentTool !== null)
        currentTool.close();
    });
    
    $toolframe.resizable({
      handles: "n,s,e,w",
      stop: function(event, ui) {
        saveState();
      }
    });

    $lengthener.appendTo('body');
    move(dock);
    
    $devtools.fadeIn(200);
  });
  
  return parent;
}(JIVOO || {}, jQuery, Cookies))

$(function() {
  if (!Object.keys) {
    Object.keys = function(obj) {
      var arr = [], key;
      for (key in obj) {
        if (obj.hasOwnProperty(key)) {
          arr.push(key);
        }
      }
      return arr;
    };
  }
  
  $.fn.prependToParent = function() {
    this.prependTo(this.parent());
  };
  
  var logTool = JIVOO.devbar.addTool('jivoo-log', 'Log', function ($content) {
    var $log = $('<div class="jivoo-console-log">');
    $.each(jivooLog, function(i, entry) {
      var $entry = $('<div class="jivoo-console-log-entry"></div>');
      var message = entry.message;
      if (entry.context.file)
        message += ' in <em>' + entry.context.file + '</em> on line <strong>'
                 + entry.context.line + '</strong>';
      $entry.html(message);
      switch (entry.level) {
        case 'info':
        case 'notice':
          $entry.css('color', '#aa2');
          break;
        case 'warning':
          $entry.css('color', '#f90');
          break;
        case 'emergency':
        case 'alert':
        case 'critical':
        case 'error':
          $entry.css('color', '#f00');
          break;
        default:
          $entry.css('color', '#99f');
          break;
      }
      if (entry.context.query)
        $entry.css('color', '#999');
      $log.append($entry);
    });
    $content.append($log);
  });
  logTool.$menuItem.children('a').append($('<span class="jivoo-devbar-count">').text(jivooLog.length));

  var requestTool = JIVOO.devbar.addTool('jivoo-request', 'Request', function ($content) {
    var $log = $('<div class="jivoo-console-log">');
    for (var key in jivooRequest) {
      var $entry = $('<div class="jivoo-console-log-entry"></div>');
      $entry.html('<strong>' + key + ':</strong> ' + JSON.stringify(jivooRequest[key]));
      $log.append($entry);
    }
    $content.append($log);
  });
  
  var sessionTool = JIVOO.devbar.addTool('jivoo-session', 'Session', function ($content) {
    var $log = $('<div class="jivoo-console-log">');
    for (var key in jivooSession) {
      var $entry = $('<div class="jivoo-console-log-entry"></div>');
      $entry.html('<strong>' + key + ':</strong> ' + JSON.stringify(jivooSession[key]));
      $log.append($entry);
    }
    $content.append($log);
  });
  sessionTool.$menuItem.children('a').append($('<span class="jivoo-devbar-count">').text(Object.keys(jivooSession).length));

  var cookieTool = JIVOO.devbar.addTool('jivoo-cookies', 'Cookies', function ($content) {
    var $log = $('<div class="jivoo-console-log">');
    for (var key in jivooCookies) {
      var $entry = $('<div class="jivoo-console-log-entry"></div>');
      $entry.html('<strong>' + key + ':</strong> ' + JSON.stringify(jivooCookies[key]));
      $log.append($entry);
    }
    $content.append($log);
  });
  cookieTool.$menuItem.children('a').append($('<span class="jivoo-devbar-count">').text(Object.keys(jivooCookies).length));
  
  // Move debug tools in front of other tools.
  cookieTool.$menuItem.prependToParent();
  sessionTool.$menuItem.prependToParent();
  requestTool.$menuItem.prependToParent();
  logTool.$menuItem.prependToParent();
  
  
  for (var i = 0; i < jivooLog.length; i++) {
    var entry = jivooLog[i];
    var message = entry.message;
    if (entry.context.file)
      message += ' in ' + entry.context.file + ' on line ' + entry.context.line;
    switch (entry.level) {
      case 'info':
      case 'notice':
        console.info(message);
        break;
      case 'warning':
        console.warn(message);
        break;
      case 'emergency':
      case 'alert':
      case 'critical':
      case 'error':
        console.error(message);
        break;
      default:
        console.log(message);
        break;
    }
  }
});
