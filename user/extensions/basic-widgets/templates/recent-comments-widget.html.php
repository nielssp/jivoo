<ul>
<?php foreach ($comments as $comment): ?>
<?php
if (empty($comment->author)) {
  $author = tr('Anonymous');
}
else {
  $website = $Html->cleanUrl($comment->website);
  if (empty($website))
    $author = h($comment->author);
  else
    $author = '<a rel="nofollow" href="' . $website . '">' . h($comment->author) . '</a>';
}
?>
<li><?php echo tr('%1 on %2', $author, $Html->link(h($comment->post->title), $comment)); ?></li>
<?php endforeach; ?>
</ul>
