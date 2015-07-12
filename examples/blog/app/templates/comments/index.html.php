<ul class="comments">
<?php foreach ($comments as $comment): ?>

<li id="comment<?php echo $comment->id; ?>">

<?php if ($Auth->isLoggedIn()): ?>
<?php echo $Form->form(array(
  'controller' => 'Comments',
  'action' => 'delete',
  $comment->postId, $comment->id
), array('method' => 'delete')); ?>
<div class="admin">
<?php echo $Html->link(tr('Edit'), array(
  'controller' => 'Comments',
  'action' => 'edit',
  $comment->postId, $comment->id
), array('class' => 'button')); ?>

<?php echo $Form->submit(tr('Delete')); ?>
</div>
<?php echo $Form->end(); ?>
<?php endif; ?>

<div class="comment">
<div class="author"><?php echo h($comment->author); ?></div>
<div class="content">
<?php echo h($comment->content); ?>
</div>
<div class="byline">
<?php
echo '<time datetime="' . date('c', $comment->created)
      . '" title="' . ldate($comment->created) . '">'
      . sdate($comment->created) . '</time>';
?>
</div>

</div>
</li>

<?php endforeach; ?>
</ul>

<?php echo $this->embed('comments/pagination.html'); ?>
