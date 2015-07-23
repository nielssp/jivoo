<?php $this->view->data->title = tr('Notifications'); ?>
 
<p class="notification-demo">
<?php echo $Jtk->button(tr('Info'), 'icon=info data-type=info'); ?>

<?php echo $Jtk->button(tr('Question'), 'icon=question data-type=question'); ?>

<?php echo $Jtk->button(tr('Success'), 'icon=checkmark data-type=success'); ?>

<?php echo $Jtk->button(tr('Warning'), 'icon=warning data-type=warning'); ?>

<?php echo $Jtk->button(tr('Error'), 'icon=close data-type=error'); ?>
</p>

<p>
Loading notification:
<?php echo $Jtk->button(tr('Start'), 'class=load-start'); ?>

<?php echo $Jtk->button(tr('Stop'), 'class=load-stop'); ?>
</p>

<p>
Apply loading screen:

<?php echo $Jtk->button(tr('Apply to block'), 'class=load-block'); ?>

<?php echo $Jtk->button(tr('Apply to content'), 'class=load-content'); ?>

<?php echo $Jtk->button(tr('Apply to body'), 'class=load-body'); ?>
</p>

<div class="grid-sm grid-1-1-1">
<div class="cell">
<div class="block" id="block">
<div class="block-header">Loading screen</div>
<div class="block-content">Lorem ipsum</div>
</div>
</div>
</div>

<script type="text/javascript">
$(function() {
  $('.notification-demo button').click(function() {
    JTK.notify('This is a notification!', $(this).data('type'));
  });
  $('.load-start').click(JTK.notifications.startLoading);
  $('.load-stop').click(JTK.notifications.stopLoading);

  $.fn.testLoadScreen = function() {
    var $screen = $('<div class="loading-screen">');
    $screen.hide();
    $screen.appendTo(this);
    $screen.fadeIn(100);
    setTimeout(function() {
      $screen.fadeOut(100, function() {
        $screen.remove()
      });
    }, 2000);
  };
  
  $('.load-block').click(function() {
    $('#block').testLoadScreen();
  });
  
  $('.load-content').click(function() {
    $('#main').testLoadScreen();
  });
  
  $('.load-body').click(function() {
    $('body').testLoadScreen();
  });
});
</script>
