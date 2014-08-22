<?php $this->extend('admin/layout.html'); ?>

<?php if (!$post): ?>

<div class="flash flash-warn"><?php echo tr('The post does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->formFor($post, array(), array('class' => 'publish')); ?>

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

<?php echo $Form->textarea('content', array(
  'placeholder' => tr('Content'),
  'class' => 'content'
)); ?>

</div>

<div class="settings">

  <div class="field">
    <?php echo $Form->label('name', tr('Permalink')); ?>
    <div class="permalink">
      <?php echo str_replace(
        '%name%', $Form->text('name'),
        $this->link($post->getModel()->create())
      ); ?>
    </div>
  </div>

  <div class="field">
    <?php echo $Form->label('status'); ?>
    <?php echo $Form->selectOf('status'); ?>
  </div>

  <div class="field">
    <label>Tags</label>
    <input type="text" value="not implemented" disabled />
  </div>

  <div class="field">
    <label>Comments</label>
    <?php echo $Form->hidden('commenting', array('value' => false)); ?>
    <?php echo $Form->checkbox('commenting', true); ?>
    <?php echo $Form->checkboxLabel('commenting', true, tr('Allow comments')); ?>
  </div>
</div>


<?php echo $Form->end(); ?>

<?php endif; ?>