$(function() {
  $('.toggle-menu').click(function() {
    $('body').toggleClass('menu-open');
    
  });
  $('nav > ul > li > a').click(function() {
    $('nav > ul > li > a').removeClass('current');
    $(this).addClass('current');
  });
});
