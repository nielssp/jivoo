// Notifications module
var JTK = (function(parent, $) {
  var my = parent.notifications = parent.notifications || {};
  
  var themeTemplate = function(notification) {
    return function(data) {
      return '<div class="icon"></div><div class="message">' + data.message + '</div>';
    };
  };
  
  my.onReceive(function(notification) {
    $.amaran({
      content: {
        themeName: 'notification ' + notification.type,
        message: notification.message
      },
      themeTemplate: themeTemplate(notification),
      position: 'top right',
      inEffect: 'slideTop',
      delay: 6000,
      resetTimeout: true
    });
  });

  var $loading;
  
  $(function() {
    $.amaran({
      content: {
        themeName: 'notification loading',
        message: $('body').data('loadmsg')
      },
      sticky: true,
      themeTemplate: themeTemplate(true),
      position: 'top right',
      inEffect: 'slideTop',
      closeOnClick: false
    });
    $loading = $('.notification.loading');
    $loading.hide();
  });
  
  
  my.startLoading = function() {
    if ($loading)
      $loading.fadeIn(200);
  };
  my.stopLoading = function() {
    if ($loading)
      $loading.fadeOut(200);
  };
  
  return parent;
})(JTK || {}, jQuery);

// Dialog module
var JTK = (function(parent, $) {
  var my = parent.dialog = parent.dialog || {};
  
  my.close = function() {
    $.magnificPopup.close();
  };
  
  my.open = function(element, modal) {
    if (modal === undefined) modal = false;
    if (modal) {
      $(element).find('.block-toolbar [data-close="dialog"]').hide();
    }
    else {
      $(element).find('.block-toolbar [data-close="dialog"]').show();
      $(element).find('.block-toolbar [data-close="dialog"]').click(function() {
        my.close();
        return false;
      });
    }
    $(element).show();
    $.magnificPopup.open({
      closeBtnInside: false,
      showCloseBtn: false,
      modal: modal,
      alignTop: true,
      items: {
        src: element,
        type: 'inline'
      }
    });
  };
  
  my.ajax = function(url, modal) {
    var $dialog = $('<div class="block dialog"><div class="block-content"></div></div>');
    $dialog.startLoading();
    my.close();
    my.open($dialog, modal);
    parent.ajax({
      url: url,
      type: 'html',
      showLoading: false,
      success: function(content) {
        var $content = $(content).children();
        $content.hide();
        $dialog.find('.block-content').replaceWith($content);
        if (modal)
          $content.find('.block-toolbar [data-close="dialog"]').hide();
        parent.init($content);
        $content.filter('.block-content').slideDown(400);
        $content.fadeIn(400, function() {
          $dialog.stopLoading();
        });
      },
      error: function() {
        my.close();
      }
    });
  };
  
  var old = parent.init || function() {};
  parent.init = function(element) {
    $(element).find('[data-open="dialog"]').each(function() {
      var url = $(this).attr('href');
      var modal = $(this).is('[data-modal]');
      $(this).click(function() {
        my.ajax(url, modal);
        return false;
      });
    });
    $(element).find('[data-close="dialog"]').click(function() {
      my.close();
      return false;
    });
    old(element);
  };
  
  return parent;
})(JTK || {}, jQuery);


$(function() {
  var old = JTK.init || function() {};
  JTK.init = function(element) {
    $(element).find('[title]').tooltip({
      show: false,
      hide: false,
      position: {
        my: 'center bottom-12',
        at: 'center top',
        using: function(position, feedback) {
          $(this).css(position).addClass(feedback.vertical)
            .addClass(feedback.horizontal);
        }
      }
    });

    $(element).tooltip({
      items: '[data-error]',
      content: function() {
        return $(this).data('error');
      },
      show: false,
      hide: false,
      position: {
        my: 'right middle',
        at: 'right-4 middle+4'
      }
    });
    old(element);
  };
  $.fn.jtkInit = function() {
    JTK.init(this);
  };
  
  $(document).jtkInit();
  
  
  $('.toggle-menu').click(function() {
    $('body').toggleClass('menu-open');
  });
  $('#main').click(function() {
    $('body').removeClass('menu-open');
  });
  $('nav > ul > li > ul > li > a').click(function() {
    $('nav > ul > li > ul > li > a').removeClass('current');
    $(this).addClass('current');
  });
  
  $.fn.startLoading = function() {
    var $screen = $('<div class="loading-screen">');
    $screen.hide();
    $screen.appendTo(this);
    $screen.fadeIn(100);
  };
  $.fn.stopLoading = function() {
    var $screen = $(this).find('.loading-screen');
    $screen.fadeOut(100, function() {
      $screen.remove()
    });
  };
});
