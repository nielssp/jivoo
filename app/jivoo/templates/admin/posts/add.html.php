<?php
$this->extend('admin/layout.html');
$this->import('jquery.js', 'jquery-ui.js', 'permalinks.js', 'tags.js');
?>

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

<?php if ($post->isNew()): ?>

<?php echo $Form->text('title', array(
  'placeholder' => tr('Title'),
  'class' => 'title',
  'data-auto-permalink' => $Form->id('name')
)); ?>

<?php else: ?>

<?php echo $Form->text('title', array(
  'placeholder' => tr('Title'),
  'class' => 'title'
)); ?>

<?php endif; ?>

<?php echo $Editor->get('content', array(
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
    <label for="Post_addTag">Tags</label>
    <?php echo $Form->hidden('jsonTags'); ?>
    <div class="tags">
      <?php foreach ($tags as $tag): ?>
        <span class="tag" data-name="<?php echo $tag->name; ?>">
          <?php echo $tag->tag; ?>
          <a href="#" class="tag-remove">
            <span class="icon-remove"></span>
          </a>
        </span>
      <?php endforeach; ?>
    </div>
    <input type="text" id="Post_addTag" />
    <input type="button" id="Post_addTag_button" value="<?php echo tr('Add'); ?>" />
  </div>

  <div class="field">
    <label>Comments</label>
    <?php echo $Form->hidden('commenting', array('value' => false)); ?>
    <?php echo $Form->checkbox('commenting', true); ?>
    <?php echo $Form->checkboxLabel('commenting', true, tr('Allow comments')); ?>
  </div>

  <div class="field">
    <label>Format</label>
    <?php echo $Format->selectFormat('content'); ?>
  </div>
</div>


<?php echo $Form->end(); ?>

<?php endif; ?>
