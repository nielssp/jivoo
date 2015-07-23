<?php $this->view->data->title = tr('Dialogs'); ?>
 
<div class="block">
  <div class="block-header"><h2>Dialogs</h2></div>
  <div class="block-content">
<p class="dialogs-demo">
<?php echo $Icon->button(tr('Default'), 'info', array('data-type' => '')); ?>

<?php echo $Icon->button(tr('Muted'), 'info', array('data-type' => 'muted')); ?>

<?php echo $Icon->button(tr('Primary'), 'info', array('data-type' => 'primary')); ?>

<?php echo $Icon->button(tr('Light'), 'info', array('data-type' => 'light')); ?>

<?php echo $Icon->button(tr('Dark'), 'info', array('data-type' => 'dark')); ?>

<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warning')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>
  </div>
</div>

<div class="block">
  <div class="block-header"><h2>Modals</h2></div>
  <div class="block-content">
<p class="modals-demo">
<?php echo $Icon->button(tr('Default'), 'info', array('data-type' => '')); ?>

<?php echo $Icon->button(tr('Muted'), 'info', array('data-type' => 'muted')); ?>

<?php echo $Icon->button(tr('Primary'), 'info', array('data-type' => 'primary')); ?>

<?php echo $Icon->button(tr('Light'), 'info', array('data-type' => 'light')); ?>

<?php echo $Icon->button(tr('Dark'), 'info', array('data-type' => 'dark')); ?>

<?php echo $Icon->button(tr('Info'), 'info', array('data-type' => 'info')); ?>

<?php echo $Icon->button(tr('Success'), 'checkmark', array('data-type' => 'success')); ?>

<?php echo $Icon->button(tr('Warning'), 'warning', array('data-type' => 'warning')); ?>

<?php echo $Icon->button(tr('Error'), 'close', array('data-type' => 'error')); ?>
</p>
  </div>
</div>


<div class="block">
  <div class="block-header"><h2>Ajax content</h2></div>
  <div class="block-content">
    <p>
<?php echo $Icon->link(tr('Fetch'), 'path:ui/elements/dialog', null, null, array('data-open' => 'dialog')); ?>
    </p>
  </div>
</div>

<div class="block dialog" id="dialog" style="display: none">
<div class="block-header">A dialog 
  <div class="block-toolbar">
    <?php echo $Icon->iconLink('Close', 'void:', 'close', array('data-close' => 'dialog')); ?>
  </div>
</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="block-footer">
  <?php echo $Icon->button('Cancel', 'close'); ?>
  <?php echo $Icon->button('OK', 'checkmark', array('class' => 'button-primary')); ?>
</div>
</div>


<script type="text/javascript">
$(function() {
  var $popup = $('#dialog').clone();
  $popup.show();
  $('.dialogs-demo button').click(function() {
    var $this = $popup.clone();
    $this.addClass('block-' + $(this).data('type'));
    $this.find('button').click(JTK.dialog.close);
    JTK.dialog.open($this);
  });
  $('.modals-demo button').click(function() {
    var $this = $popup.clone();
    $this.addClass('block-' + $(this).data('type'));
    $this.find('button').click(JTK.dialog.close);
    JTK.dialog.open($this, true);
  });
});
</script>
