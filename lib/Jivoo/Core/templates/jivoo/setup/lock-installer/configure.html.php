<?php echo $Form->formFor($form, null); ?>

<p>
<?php echo tr('The username and password will be used to access this installer.'); ?>
</p>

<?php echo $Form->standardField('username'); ?>

<?php echo $Form->standardField('password', 'password'); ?>

<?php echo $Form->standardField('confirmPassword', 'password'); ?>
