<?php echo $Form->formFor($message, 'SendMessage'); ?>

<p>
<?php echo $Form->text('message'); ?>
<?php echo $Form->submit(tr('Send')); ?>
</p>

<?php echo $Form->end(); ?>