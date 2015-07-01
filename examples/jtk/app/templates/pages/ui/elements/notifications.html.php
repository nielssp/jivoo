<?php $this->view->data->title = tr('Notifications'); ?>
 
<p class="notification-demo">
<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Question'), 'question', array('data-type' => 'question')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warn')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>

<p>
Loading:
<?php echo $Icon->button(tr('Start'), null, array('class' => 'load-start')); ?>

<?php echo $Icon->button(tr('Stop'), null, array('class' => 'load-stop')); ?>
</p>

<script type="text/javascript">
$(function() {
  $('.notification-demo button').click(function() {
    JIVOO.notifications.send('This is a notification!', $(this).data('type'));
  });
  $('.load-start').click(JIVOO.notifications.startLoading);
  $('.load-stop').click(JIVOO.notifications.stopLoading);
});
</script>