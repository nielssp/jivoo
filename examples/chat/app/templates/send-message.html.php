<?php echo $Form->formFor($message, 'SendMessage'); ?>

<p>
<?php echo $Form->text('message', array('placeholder' => tr('Send a message'))); ?>
<input type="button" id="change-name" value="<?php echo tr('Change name') ?>" />
<?php echo $Form->submit(tr('Send')); ?>
</p>

<?php echo $Form->end(); ?>