$(function() {
  $('.toggle-menu').click(function() {
    $('body').toggleClass('menu-open');
  });
  $('nav > ul > li > a').click(function() {
    $('nav > ul > li > a').removeClass('current');
    $(this).addClass('current');
  });
  
  var themeTemplate = function(data) {
    return '<div class="icon"></div><div class="message">' + data.message + '</div>';
  };
  
  JIVOO.notifications.onReceive(function(notification) {
    $.amaran({
      content: {
        themeName: 'notification ' + notification.type,
        message: notification.message
      },
      themeTemplate: themeTemplate,
      position: 'top right',
      inEffect: 'slideTop',
      resetTimeout: true
    });
  });
  
  $.amaran({
    content: {
      themeName: 'notification loading',
      message: $('body').data('loadmsg')
    },
    sticky: true,
    themeTemplate: themeTemplate,
    position: 'top right',
    inEffect: 'slideTop',
    closeOnClick: false,
    beforeStart: function(){
      console.log(this)
    }
  });
  
  var $loading = $('.notification.loading');
  $loading.hide();
  
  JIVOO.notifications.startLoading = function() {
    $loading.fadeIn(200);
  };
  JIVOO.notifications.stopLoading = function() {
    $loading.fadeOut(200);
  };
});
