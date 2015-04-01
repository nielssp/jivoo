<?php echo $Form->form(); ?>

<p><?php echo $confirmation; ?></p>

<?php echo $Form->submit(tr('OK')); ?>

<?php echo $Form->submit(tr('Cancel'), array('name' => 'cancel')); ?>

<?php echo $Form->end(); ?>
