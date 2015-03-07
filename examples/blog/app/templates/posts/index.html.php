<?php foreach ($posts as $post): ?>

<div class="post">

<h1><?php echo $Html->link(h($post->title), $post); ?></h1>

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

<?php endforeach; ?>

<?php echo $this->embed('posts/pagination.html'); ?>
