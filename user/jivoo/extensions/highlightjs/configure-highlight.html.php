<?php echo $Form->formFor($settings); ?>

<div class="field">
<?php echo $Form->label('theme', tr('Theme')); ?>
<?php echo $Form->selectOf('theme', $themes); ?>
</div>

<?php echo $Form->submit(tr('Save')); ?>

<?php echo $Form->end(); ?>

