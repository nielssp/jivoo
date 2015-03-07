<?php foreach ($posts as $post): ?>

<div class="post">

<h1><?php echo $Html->link(h($post->title), $post); ?></h1>

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
