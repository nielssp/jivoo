<ul>
<?php foreach ($comments as $comment): ?>
<li><?php echo tr(
  '%1 on %2',
  $comment->author,
  $Html->link(h($comment->post->title), $comment)
); ?></li>
<?php endforeach; ?>
</ul>
