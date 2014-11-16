<?php $this->extend('layout.html'); ?>

<?php foreach ($posts as $post) : ?>

<div class="post">
  <h1>
    <?php echo $Html->link(h($post->title), $post); ?>
  </h1>
<?php echo $Format->html($post, 'content'); ?>

<div class="byline">
<?php 
if (isset($post->user))
  echo h($post->user->username) . ' | ';
?>
<?php 
if (isset($post->published))
  echo '<time datetime="' . date('c', $post->published)
        . '" title="' . ldate($post->published) . '">'
        . sdate($post->published) . '</time>' . ' | ';
?> 
<?php
$comments = $post->comments->where('status = %CommentStatus', 'approved')->count();
if ($comments == 0) {
  echo $Html->link(
    tr('Leave a comment'),
    $this->mergeRoutes($post, array('fragment' => 'comment'))
  );
}
else {
  echo $Html->link(
    tn('%1 comments', '%1 comment', $comments),
    $this->mergeRoutes($post, array('fragment' => 'comments'))
  );
}
?>
</div>

</div>
<?php endforeach; ?>

<?php echo $this->embed('posts/pagination.html'); ?>
