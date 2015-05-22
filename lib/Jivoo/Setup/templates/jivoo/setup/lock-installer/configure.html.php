<?php $this->layout('setup/layout.html'); ?>

<?php echo $Form->formFor($form, null); ?>

<p>
<?php echo tr('.')?>
</p>

<?php echo $Form->standardField('username'); ?>

<?php echo $Form->standardField('password', 'password'); ?>

<?php echo $Form->standardField('confirmPassword', 'password'); ?>
