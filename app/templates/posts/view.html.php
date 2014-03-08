<?php $this->extend('layout.html'); ?>

<h2><?php echo h($post->title); ?></h2>

<p>Published <?php echo fdate($post->createdAt); ?>
- <?php echo ftime($post->createdAt); ?>
  <?php if ($author = $post->user) : ?>
  by <?php echo $author->username; ?>
  <?php endif; ?>
  <a href="#comment">
    (<?php echo tr('Leave a comment'); ?>)
  </a>
</p>
<?php echo $post->content; ?>

<?php if (isset($post->tags)) : ?>
<h3>Tags</h3>
<?php
  foreach ($post->tags as $tag) {
    echo $Html->link(h($tag->tag), $tag) . ' ';
  }
endif;
?>

<h3>Comments</h3>
<?php
foreach ($post->comments as $comment) :
?>

<div style="border-left:1px solid #000; padding-left:10px; margin-left: 20px">

<p>
<?php echo $Html->link('#', $comment); ?>
Published by <?php
  if ($comment->website == '')
    echo $comment->author;
  else
    echo '<a href="' . $comment->website . '">' . $comment->author . '</a>';
             ?> on <?php echo $comment->formatDate(); ?> -
<?php echo $comment->formatTime(); ?>
</p>
<?php echo $comment->content; ?>
</div>
<?php
endforeach;
?>

