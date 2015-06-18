var JIVOO = (function(parent, $, Cookies) {
  var my = parent.devbar = parent.devbar || {};
  
  var tools = [];
  
  var toolQueue = [];
  
  var $devtools =  null;
  var $devbar = null;
  var $tools = null;
  var $toolframe = null;
  
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
  
  function Tool(id, name, createContent) {
    this.id = id;
    this.name = name;
    this.createContent = createContent;
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
    if (animate)
      $toolframe.show('blind');
    else
      $toolframe.show();
  };
  Tool.prototype.close = function() {
    currentTool = null;
    $toolframe.hide('blind');
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
    var size = confGet('size', 150);
    size = Math.max(size, 0);
    size = Math.min(size, $(window).height() - 100);
    $toolframe.height(size);
    
    if (confGet('hide', 'false') === 'true')
      $hide.prop('checked', true);
    if (confGet('fade', 'false') === 'true')
      $fade.prop('checked', true);

    if ($fade.is(':checked'))
      $devtools.css('opacity', 0.4);
  };
  
  var saveState = function() {
    if (!$devbar)
      throw 'Jivoo Devbar not initialized!';
    confSet('size', $toolframe.height());
    
    confSet('hide', $hide.is(':checked') ? 'true' : 'false');
    confSet('fade', $fade.is(':checked') ? 'true' : 'false');
  };
  
  my.toTop = function() {
    var zmax = 0;
    $('*').each(function() {
      var cur = parseInt($(this).css('z-index'));
      zmax = cur > zmax ? cur : zmax;
    });
    $devtools.css('z-index', zmax + 1);
  };
  
  
  $(function() {
    $devtools = $('#jivoo-dev-tools');
    $toolframe = $devtools.find('.jivoo-dev-frame');
    $devbar = $devtools.children('.jivoo-devbar');
    $tools = $devbar.children('.jivoo-devbar-tools');

    $fade = $devbar.find('.jivoo-devbar-fade');
    $hide = $devbar.find('.jivoo-devbar-hide');
    
    $fade.click(saveState)
    $hide.click(saveState)
    
    my.toTop();

    loadState();
    saveState();
    updateTools();
    
    $devtools.fadeIn(200);
    
    $devtools.mouseover(function() {
      if ($fade.is(':checked'))
        $devtools.animate({ opacity: 1 }, { duration: 200, queue: false });
    });
    $devtools.mouseout(function() {
      if ($fade.is(':checked'))
        $devtools.animate({ opacity: 0.4 }, { duration: 200, queue: false });
    });

    $devbar.draggable({
      scroll: false,
      revert: true,
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
      }
    });
    $toolframe.resizable({
      handles: {'n' : '.ui-resizable-handle'},
      stop: function(event, ui) {
        saveState();
      }
    });
  });
  
  return parent;
}(JIVOO || {}, jQuery, Cookies))

$(function() {
  var logTool = JIVOO.devbar.addTool('jivoo-log', 'Log', function ($content) {
    var $log = $('<div class="jivoo-console-log">');
    jivooLog.forEach(function(entry) {
      var $entry = $('<div class="jivoo-console-log-entry"></div>');
      var message = entry.message;
      if (entry.file)
        message += ' in <em>' + entry.file + '</em> on line <strong>' + entry.line + '</strong>';
      $entry.html(message);
      switch (entry.type) {
      case 1: // QUERY
        $entry.css('color', '#999');
        break;
      case 2: // DEBUG
        $entry.css('color', '#99f');
        break;
      case 4: // NOTICE
        $entry.css('color', '#aa2');
        break;
      case 8: // WARNING
        $entry.css('color', '#f90');
        break;
      case 16: // ERROR
        $entry.css('color', '#f00');
        break;
      }
      $log.append($entry);
    });
    $content.append($log);
  });
  
  jivooLog.forEach(function(entry) {
    var message = entry.message;
    if (entry.file)
      message += ' in ' + entry.file + ' on line ' + entry.line;
    switch (entry.type) {
    case 1: // QUERY
      console.debug(message);
      break;
    case 2: // DEBUG
      console.log(message);
      break;
    case 4: // NOTICE
      console.log(message);
      break;
    case 8: // WARNING
      console.warn(message);
      break;
    case 16: // ERROR
      console.error(message);
      break;
    }
  });
  
  logTool.$menuItem.children('a').append($('<span class="jivoo-devbar-count">').text(jivooLog.length));
});
