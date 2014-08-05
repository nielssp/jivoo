<?php
$this->extend('setup/layout.html');
?>

<?php echo $Form->form(); ?>

<div class="field">
<?php echo $Form->label('appDir', tr('Application path')); ?>
<?php echo $Form->text('appDir'); ?>
</div>

<div class="field">
<?php echo $Form->label('libDir', tr('Library path')); ?>
<?php echo $Form->text('libDir'); ?>
</div>

<?php echo $Form->submit(tr('Install')); ?>

<?php echo $Form->end(); ?>


