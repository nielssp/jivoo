<?php
$this->extend('setup/layout.html');
?>

<?php echo $Form->formFor($user); ?>

<p><?php echo tr('Please select a username and a password.'); ?></p>

<div class="field<?php echo $Form->ifRequired('username', ' fireld-required'); ?>">
<?php echo $Form->label('username'); ?>
<?php echo $Form->text('username'); ?>
<?php echo $Form->error('username'); ?>
</div>

<div class="field<?php echo $Form->ifRequired('password', ' fireld-required'); ?>">
<?php echo $Form->label('password'); ?>
<?php echo $Form->text('password'); ?>
<?php echo $Form->error('password'); ?>
</div>

<div class="field<?php echo $Form->ifRequired('confirmPassword', ' fireld-required'); ?>">
<?php echo $Form->label('confirmPassword'); ?>
<?php echo $Form->text('confirmPassword'); ?>
<?php echo $Form->error('confirmPassword'); ?>
</div>

<div class="field<?php echo $Form->ifRequired('email', ' fireld-required'); ?>">
<?php echo $Form->label('email'); ?>
<?php echo $Form->text('email'); ?>
<?php echo $Form->error('email'); ?>
</div>

<?php echo $Form->submit(tr('Save'), array('class' => 'primary')); ?>
<?php echo $Form->submit(tr('Skip'), array('name' => 'skip')); ?>

<?php echo $Form->end(); ?>


