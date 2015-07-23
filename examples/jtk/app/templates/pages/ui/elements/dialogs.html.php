<?php $this->view->data->title = tr('Dialogs'); ?>
 
<div class="block">
  <div class="block-header"><h2>Dialogs</h2></div>
  <div class="block-content">
<p class="dialogs-demo">
<?php echo $Jtk->button(tr('Default'), 'icon=info data-type=""'); ?>

<?php echo $Jtk->button(tr('Muted'), 'icon=info data-type=muted'); ?>

<?php echo $Jtk->button(tr('Primary'), 'icon=info data-type=primary'); ?>

<?php echo $Jtk->button(tr('Light'), 'icon=info data-type=light'); ?>

<?php echo $Jtk->button(tr('Dark'), 'icon=info data-type=dark'); ?>

<?php echo $Jtk->button(tr('Info'), 'icon=info data-type=info'); ?>

<?php echo $Jtk->button(tr('Success'), 'icon=info data-type=success'); ?>

<?php echo $Jtk->button(tr('Warning'), 'icon=info data-type=warning'); ?>

<?php echo $Jtk->button(tr('Error'), 'icon=info data-type=error'); ?>
</p>
  </div>
</div>

<div class="block">
  <div class="block-header"><h2>Modals</h2></div>
  <div class="block-content">
<p class="modals-demo">
<?php echo $Jtk->button(tr('Default'), 'icon=info data-type=""'); ?>

<?php echo $Jtk->button(tr('Muted'), 'icon=info data-type=muted'); ?>

<?php echo $Jtk->button(tr('Primary'), 'icon=info data-type=primary'); ?>

<?php echo $Jtk->button(tr('Light'), 'icon=info data-type=light'); ?>

<?php echo $Jtk->button(tr('Dark'), 'icon=info data-type=dark'); ?>

<?php echo $Jtk->button(tr('Info'), 'icon=info data-type=info'); ?>

<?php echo $Jtk->button(tr('Success'), 'icon=info data-type=success'); ?>

<?php echo $Jtk->button(tr('Warning'), 'icon=info data-type=warning'); ?>

<?php echo $Jtk->button(tr('Error'), 'icon=info data-type=error'); ?>
</p>
  </div>
</div>


<div class="block">
  <div class="block-header"><h2>Ajax content</h2></div>
  <div class="block-content">
    <p>
    <?php echo $Html->link(tr('Fetch'), 'path:ui/elements/dialog', 'data-open=dialog'); ?>
    </p>
  </div>
</div>

<div class="block dialog" id="dialog" style="display: none">
<div class="block-header">A dialog 
  <div class="block-toolbar">
    <?php echo $Jtk->iconLink('Close', 'void:', 'icon=close data-close=dialog'); ?>
  </div>
</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="block-footer">
  <?php echo $Jtk->button('Cancel', 'icon=close'); ?>
  <?php echo $Jtk->button('OK', 'icon=checkmark context=primary'); ?>
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
