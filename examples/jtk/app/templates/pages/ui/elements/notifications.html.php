<?php $this->view->data->title = tr('Notifications'); ?>
 
<p class="notification-demo">
<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Question'), 'question', array('data-type' => 'question')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warn')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>

<p>
Loading notification:
<?php echo $Icon->button(tr('Start'), null, array('class' => 'load-start')); ?>

<?php echo $Icon->button(tr('Stop'), null, array('class' => 'load-stop')); ?>
</p>

<p>
Apply loading screen:

<?php echo $Icon->button(tr('Apply to block'), null, array('class' => 'load-block')); ?>

<?php echo $Icon->button(tr('Apply to content'), null, array('class' => 'load-content')); ?>

<?php echo $Icon->button(tr('Apply to body'), null, array('class' => 'load-body')); ?>
</p>

<div class="row-1-1-1">
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
    JIVOO.notifications.send('This is a notification!', $(this).data('type'));
  });
  $('.load-start').click(JIVOO.notifications.startLoading);
  $('.load-stop').click(JIVOO.notifications.stopLoading);

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