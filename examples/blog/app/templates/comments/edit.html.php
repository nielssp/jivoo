<?php echo $Form->formFor($comment, array('fragment' => 'comment', 'mergeQuery' => true)); ?>

<?php echo $Form->field('author', array('description' => tr('Your name'))); ?>

<?php echo $Form->field('content'); ?>

<div class="buttons">
<?php echo $Form->submit(tr('Save')); ?>
</div>

<?php echo $Form->end(); ?>
