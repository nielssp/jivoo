<?php $this->view->data->title = tr('Modals'); ?>
 
<p class="modals-demo">
<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Question'), 'question', array('data-type' => 'question')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warn')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>

<div class="block modal" id="block">
<div class="block-header">Block</div>
<div class="block-content">Lorem ipsum</div>
</div>

<script type="text/javascript">
$(function() {
  var $popup = $('#block').clone();
  $('#block').remove();
  $('.modals-demo button').click(function() {
    var $this = $popup.clone();
    $this.addClass('block-' + $(this).data('type'));
    $.magnificPopup.open({
      alignTop: true,
      items: {
        src: $this,
        type: 'inline'
      }
    });
  });
});
</script>