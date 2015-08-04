<?php $Form->form('path:setup'); ?>

<p>
<?php echo tr('Log in using your maintainance username and password in order to continue the installation process.')?>
</p>

<?php echo $Form->field('username', array('label' => tr('Username'))); ?>
<?php echo $Form->field('password', array('label' => tr('Password'))); ?>

<?php $this->data->form = $Form->end(); ?>