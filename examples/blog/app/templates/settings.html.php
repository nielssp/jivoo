<?php echo $Form->formFor($settings); ?>

<div class="field">
<?php echo $Form->label('title'); ?>
<?php echo $Form->text('title'); ?>
<?php echo $Form->error('title'); ?>
</div>

<div class="field">
<?php echo $Form->label('username'); ?>
<?php echo $Form->text('username'); ?>
<?php echo $Form->error('username'); ?>
</div>

<div class="field">
<?php echo $Form->label('password'); ?>
<?php echo $Form->password('password'); ?>
<?php echo $Form->error('password'); ?>
</div>

<div class="field">
<?php echo $Form->label('confirmPassword'); ?>
<?php echo $Form->password('confirmPassword'); ?>
<?php echo $Form->error('confirmPassword'); ?>
</div>

<?php echo $Form->submit(tr('Save')); ?>

<?php echo $Form->end(); ?>