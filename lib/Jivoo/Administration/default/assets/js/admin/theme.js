$(function() {
  $('.toggle-menu').click(function() {
    $('body').toggleClass('menu-open');
    
  });
  $('nav > ul > li > a').click(function() {
    $('nav > ul > li > a').removeClass('current');
    $(this).addClass('current');
  });
  var dragging = false;
  var newState = false;
  $('input[type=checkbox').mousedown(function(e) {
    dragging = true;
    newState = !this.checked;
  }).on('mouseout mouseover', function() {
    if (dragging)
      this.checked = newState;
  });
  $(window).mouseup(function() {
    dragging = false;
  });
});
