<?php $this->extend('admin/layout.html'); ?>

<?php if (!$comment): ?>

<div class="flash flash-warn"><?php echo tr('The comment does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->formFor($comment, array(), array('class' => 'publish')); ?>

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

<?php echo $Editor->get('content', array(
  'placeholder' => tr('Content'),
  'class' => 'content'
)); ?>

</div>

<div class="settings">

  <div class="field">
    <?php echo $Form->label('author'); ?>
    <?php echo $Form->text('author'); ?>
  </div>
  
  <div class="field">
    <?php echo $Form->label('email'); ?>
    <?php echo $Form->text('email'); ?>
  </div>
  
  <div class="field">
    <?php echo $Form->label('website'); ?>
    <?php echo $Form->text('website'); ?>
  </div>

  <div class="field">
    <?php echo $Form->label('status'); ?>
    <?php echo $Form->selectOf('status'); ?>
  </div>
  
  <p><?php echo tr('IP address: %1', $comment->ip); ?></p>
</div>


<?php echo $Form->end(); ?>

<?php endif; ?>
