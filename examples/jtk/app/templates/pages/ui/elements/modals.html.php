<?php $this->view->data->title = tr('Modals'); ?>
 
<p class="modals-demo">
<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Question'), 'question', array('data-type' => 'question')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warn')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>

<div class="block modal" id="block" style="display: none">
<div class="block-header">Block</div>
<div class="block-content">Lorem ipsum</div>
<div class="block-buttons">
  <?php echo $Icon->button('Cancel', 'close'); ?>
  <?php echo $Icon->button('OK', 'checkmark', array('class' => 'primary')); ?>
</div>
</div>

<p><em>todo: add close button to block header</em></p>
<p><em>todo: optional footer with buttons to block</em></p>

<script type="text/javascript">
$(function() {
  var $popup = $('#block').clone();
  $popup.show();
  $('.modals-demo button').click(function() {
    var $this = $popup.clone();
    $this.addClass('block-' + $(this).data('type'));
    $this.find('button').click($.magnificPopup.close);
    $.magnificPopup.open({
      closeBtnInside: false,
      prependTo: $('#main'),
      alignTop: true,
      items: {
        src: $this,
        type: 'inline'
      }
    });
  });
});
</script>
