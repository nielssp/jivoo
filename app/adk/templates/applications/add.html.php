<?php $this->extend('admin/layout.html'); ?>

<p><strong>Warning:</strong> Only add applications from trusted sources.</p>

<?php echo $Form->form(); ?>

<div class="field">
<?php echo $Form->label('path', tr('Absolute path to %1-file', '<code>app.php</code>')); ?>
<?php echo $Form->text('path'); ?>
</div>

<?php echo $Form->submit(tr('Add')); ?>

<?php echo $Form->end(); ?>