<?php $this->view->data->title = tr('Modals'); ?>
 
<p class="modals-demo">
<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Question'), 'question', array('data-type' => 'question')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warn')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>

<div class="block modal" id="block" style="display: none">
<div class="block-header">A modal
  <div class="block-toolbar">
    <?php echo $Html->link($Icon->icon('close'), 'null:', array('class' => 'close')); ?>
  </div>
</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="block-buttons">
  <?php echo $Icon->button('Cancel', 'close'); ?>
  <?php echo $Icon->button('OK', 'checkmark', array('class' => 'primary')); ?>
</div>
</div>

<p><em>todo: add close button to block header</em></p>


<script type="text/javascript">
$(function() {
  var $popup = $('#block').clone();
  $popup.show();
  $('.modals-demo button').click(function() {
    var $this = $popup.clone();
    $this.addClass('block-' + $(this).data('type'));
    $this.find('button').click($.magnificPopup.close);
    $this.find('.block-toolbar a.close').click($.magnificPopup.close);
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
