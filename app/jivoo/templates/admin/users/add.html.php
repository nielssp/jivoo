<?php $this->extend('admin/layout.html'); ?>

<?php if (!$newUser): ?>

<div class="flash flash-warn"><?php echo tr('The user does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->formFor($newUser, array(), array('class' => 'publish')); ?>

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
  <?php echo $Form->label('username'); ?>
  <?php echo $Form->text('username'); ?>
</div>


<div class="field">
  <?php echo $Form->label('email'); ?>
  <?php echo $Form->text('email'); ?>
</div>

<div class="field">
  <?php echo $Form->label('password'); ?>
  <?php echo $Form->password('password'); ?>
</div>

<div class="field">
  <?php echo $Form->label('confirmPassword'); ?>
  <?php echo $Form->password('confirmPassword'); ?>
</div>


<?php echo $Form->end(); ?>

<?php endif; ?>