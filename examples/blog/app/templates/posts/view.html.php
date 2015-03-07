<div class="post">

<?php if ($Auth->isLoggedIn()): ?>
<?php echo $Form->form(array(
  'action' => 'delete',
  $post->id
), array('method' => 'delete')); ?>
<div class="admin">
<?php echo $Html->link(tr('Edit'), array(
  'action' => 'edit',
  $post->id
), array('class' => 'button')); ?>

<?php echo $Form->submit(tr('Delete')); ?>
</div>
<?php echo $Form->end(); ?>
<?php endif; ?>

<?php echo $post->content; ?>

<div class="byline">
<?php
echo '<time datetime="' . date('c', $comment->created)
      . '" title="' . ldate($comment->created) . '">'
      . sdate($comment->created) . '</time>';
?>
</div>

</div>

<h2 id="comments"><?php echo tr('Comments'); ?></h2>

<?php echo $Snippet('Comments\Index', $post->id); ?>

<h2 id="comment"><?php echo tr('Leave a comment'); ?></h2>

<?php echo $Snippet('Comments\Add', $post->id); ?>
