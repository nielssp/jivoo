<?php echo $Form->form('path:setup'); ?>

<p>
<?php echo tr('Log in using your maintainance username and password in order to continue the installation process.')?>
</p>

<div class="field">
<?php echo $Form->label('username', tr('Username')); ?>
<?php echo $Form->text('username'); ?>
</div>
<div class="field">
<?php echo $Form->label('password', tr('Password')); ?>
<?php echo $Form->password('password'); ?>
</div>
