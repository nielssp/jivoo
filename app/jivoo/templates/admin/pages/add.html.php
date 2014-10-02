<?php $this->extend('admin/layout.html'); ?>

<?php if (!$page): ?>

<div class="flash flash-warn"><?php echo tr('The page does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->formFor($page, array(), array('class' => 'publish')); ?>

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

<div class="article">

<?php echo $Form->text('title', array(
  'placeholder' => tr('Title'),
  'class' => 'title'
)); ?>

<?php echo $Editor->get('content', array(
  'placeholder' => tr('Content'),
  'class' => 'content'
)); ?>

</div>

<div class="settings">

  <div class="field">
    <?php echo $Form->label('name', tr('Permalink')); ?>
    <div class="permalink">
      <?php echo $this->link(null); ?>
      <?php echo $Form->text('name'); ?>
    </div>
  </div>

  <div class="field">
    <label>Status</label>
    <?php echo $Form->hidden('published', array('value' => false)); ?>
    <?php echo $Form->checkbox('published', true); ?>
    <?php echo $Form->checkboxLabel('published', true, tr('Published')); ?>
  </div>
</div>


<?php echo $Form->end(); ?>

<?php endif; ?>