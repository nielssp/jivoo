<?php echo $Form->formFor($comment); ?>

<div class="field">
<?php echo $Form->label('author'); ?>
<?php echo $Form->text('author'); ?>
<?php echo $Form->error('author'); ?>
</div>

<div class="field">
<?php echo $Form->label('content'); ?>
<?php echo $Form->textarea('content'); ?>
<?php echo $Form->error('content'); ?>
</div>

<?php echo $Form->submit(tr('Save')); ?>

<?php echo $Form->end(); ?>