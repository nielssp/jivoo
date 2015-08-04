<?php $Form->formFor($user, null); ?>

<p>
<?php echo tr('The username and password will be used to access this installer.'); ?>
</p>

<?php echo $Form->field('username'); ?>

<?php echo $Form->field('password'); ?>

<?php echo $Form->field('confirmPassword'); ?>

<?php $this->data->form = $Form->end(); ?>
