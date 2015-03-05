<?php echo $Form->formFor($post); ?>

<div class="field">
<?php echo $Form->label('title'); ?>
<?php echo $Form->text('title'); ?>
<?php echo $Form->error('title'); ?>
</div>

<div class="field">
<?php echo $Form->label('content'); ?>
<?php echo $Form->textarea('content'); ?>
<?php echo $Form->error('content'); ?>
</div>

<?php echo $Form->submit(tr('Create')); ?>

<?php echo $Form->end(); ?>