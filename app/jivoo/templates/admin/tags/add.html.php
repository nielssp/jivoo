<?php
$this->extend('admin/layout.html');
$this->import('jquery.js', 'jquery-ui.js', 'permalinks.js', 'tags.js');
?>

<?php if (!$tag): ?>

<div class="flash flash-warn"><?php echo tr('The tag does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->formFor($tag, array(), array('class' => 'publish')); ?>

<div class="toolbar">
  <button type="submit" class="primary" name="save">
    <span class="icon icon-disk"></span>
    <span class="label">Save</span>
  </button>
  <button type="submit" name="save-close">
    <span class="icon icon-checkmark"></span>
    <span class="label">Save &amp; close</span>
  </button>
  <button type="submit" name="save-new">
    <span class="icon icon-plus"></span>
    <span class="label">Save &amp; new</span>
  </button>
</div>

<div class="field">
<?php echo $Form->label('tag'); ?>
<?php if ($tag->isNew()): ?>
<?php echo $Form->text('tag', array(
  'data-auto-permalink' => $Form->id('name')
)); ?>
<?php else: ?>
<?php echo $Form->text('tag'); ?>
<?php endif; ?>
</div>


<div class="field">
  <?php echo $Form->label('name'); ?>
  <?php echo $Form->text('name'); ?>
</div>

<?php echo $Form->end(); ?>

<?php endif; ?>