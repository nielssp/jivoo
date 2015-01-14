<?php $this->extend('admin/layout.html'); ?>


<?php echo $Form->formFor($settings); ?>

<div class="field">
<?php echo $Form->label('title', tr('Website title')); ?>
<?php echo $Form->text('title'); ?>
</div>

<div class="field">
<?php echo $Form->label('subtitle', tr('Website subtitle')); ?>
<?php echo $Form->text('subtitle'); ?>
</div>

<div class="buttons">
  <button type="submit" class="primary" name="save">
    <span class="icon icon-disk"></span>
    <span class="label"><?php echo tr('Save'); ?></span>
  </button>
</div>

<?php echo $Form->end();?>