<?php
// app/templates/posts/add.html.php
$this->extend('layout.html');
?>

<?php foreach ($this->messages as $message): ?>

<?php if ($message->type == 'alert'): ?>
<div class="alert alert-danger"><?php echo $message->message; ?></div>
<?php else: ?>
<div class="alert alert-success"><?php echo $message->message; ?></div>
<?php endif; ?>
<?php $message->delete(); ?>
<?php endforeach; ?>

<h1>Add post</h1>

<?php echo $Form->begin($post); ?>

<div class="form-group">
<?php echo $Form->label('title'); ?>
<?php echo $Form->field('title', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
<?php echo $Form->label('content'); ?>
<?php echo $Form->field('content', array('class' => 'form-control')); ?>
</div>

<?php echo $Form->submit('Add', 'submit', array('class' => 'btn btn-default')); ?>

<?php echo $Form->end(); ?>
