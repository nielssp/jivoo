
<?php echo $Form->formFor($settings); ?>

<div class="field">
<?php echo $Form->label('id', tr('Analytics id')); ?>
<?php echo $Form->text('id'); ?>
</div>

<?php echo $Form->submit(tr('Save')); ?>

<?php echo $Form->end(); ?>