$(function() {
  $('.toggle-menu').click(function() {
    $('body').toggleClass('menu-open');
  });
  $('nav > ul > li > a').click(function() {
    $('nav > ul > li > a').removeClass('current');
    $(this).addClass('current');
  });
  
  JIVOO.notifications.themeTemplate = function(notification) {
    return function(data) {
      return '<div class="icon"></div><div class="message">' + data.message + '</div>';
    };
  };
  
  JIVOO.notifications.onReceive(function(notification) {
    $.amaran({
      content: {
        themeName: 'notification ' + notification.type,
        message: notification.message
      },
      themeTemplate: JIVOO.notifications.themeTemplate(notification),
      position: 'top right',
      inEffect: 'slideTop',
      delay: 6000,
      resetTimeout: true
    });
  });
  
  $.amaran({
    content: {
      themeName: 'notification loading',
      message: $('body').data('loadmsg')
    },
    sticky: true,
    themeTemplate: JIVOO.notifications.themeTemplate(true),
    position: 'top right',
    inEffect: 'slideTop',
    closeOnClick: false
  });
  
  var $loading = $('.notification.loading');
  $loading.hide();
  
  JIVOO.notifications.startLoading = function() {
    $loading.fadeIn(200);
  };
  JIVOO.notifications.stopLoading = function() {
    $loading.fadeOut(200);
  };
  
  $('[title]').tooltip({
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

  $(document).tooltip({
    items: '[data-error]',
    content: function() {
      return $(this).data('error');
    },
    show: false,
    hide: false,
    position: {
      my: 'right top',
      at: 'right-4 top+4'
    }
  });
});
