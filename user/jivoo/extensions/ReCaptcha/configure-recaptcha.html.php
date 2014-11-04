
<?php echo $Form->formFor($settings); ?>

<div class="field">
<?php echo $Form->label('publicKey', tr('Public key')); ?>
<?php echo $Form->text('publicKey'); ?>
</div>

<div class="field">
<?php echo $Form->label('privateKey', tr('Private key')); ?>
<?php echo $Form->text('privateKey'); ?>
</div>

<?php echo $Form->submit(tr('Save')); ?>

<?php echo $Form->end(); ?>