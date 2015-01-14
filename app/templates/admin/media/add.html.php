<?php $this->extend('admin/layout.html'); ?>

<?php echo $Form->form(array(), array('enctype' => 'multipart/form-data')); ?>

<div class="field">
<?php echo $Form->label('file', tr('File')); ?>
<?php echo $Form->file('file'); ?>
</div>

<?php echo $Form->submit(tr('Upload')); ?>

<?php echo $Form->end(); ?>