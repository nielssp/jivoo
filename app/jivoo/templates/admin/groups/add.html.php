<?php $this->extend('admin/layout.html'); ?>

<?php if (!$group): ?>

<div class="flash flash-warn"><?php echo tr('The group does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->formFor($group, array(), array('class' => 'publish')); ?>

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
  <?php echo $Form->label('name'); ?>
  <?php echo $Form->text('name'); ?>
</div>

<div class="field">
  <?php echo $Form->label('title'); ?>
  <?php echo $Form->text('title'); ?>
</div>

<?php echo $Form->end(); ?>

<?php endif; ?>