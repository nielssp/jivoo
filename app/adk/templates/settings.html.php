<?php $this->extend('admin/layout.html'); ?>

<?php echo $Form->form(); ?>

<div class="field">
<?php echo $Form->label('username', tr('Username')); ?>
<?php echo $Form->text('username'); ?>
</div>

<div class="field">
<?php echo $Form->label('password', tr('Password')); ?>
<?php echo $Form->password('password'); ?>
</div>

<div class="field">
<?php echo $Form->label('confirmPassword', tr('Confirm password')); ?>
<?php echo $Form->password('confirmPassword'); ?>
</div>

<?php echo $Form->submit(tr('Save')); ?>

<?php echo $Form->end(); ?>