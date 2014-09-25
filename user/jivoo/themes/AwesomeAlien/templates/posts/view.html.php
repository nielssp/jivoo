<?php $this->extend('layout.html'); ?>
<div class="post">
<h1><?php echo h($post->title); ?></h1>
<?php if ($post->status != 'published') : ?>
<p><strong>This post is a draft and is not visible to the pulic.</strong></p>
<?php endif; ?>

<?php echo $post->content; ?>

<?php
$tags = array();
foreach ($post->tags as $tag) {
  $tags[] = $Html->link(h($tag->tag), $tag);
}
?>
<div class="byline">
<?php
if (count($tags) > 0) {
  echo tr('Posted on %2 and tagged with %1{, }{ and }',
    $tags, fdate($post->createdAt));
}
else {
  echo tr('Posted on %1', fdate($post->createdAt));
}
?>
 | 
<?php echo $Html->link(
  tr('Leave a comment'),
  $this->mergeRoutes($post, array('fragment' => 'comment'))
); ?>
</div>

<?php $replying = false; ?>

</div>
<?php
if ($Pagination->getCount() > 0) :
?>
<h2 id="comments"><?php echo tn('%1 comments', '%1 comment',
    $Pagination->getCount()); ?></h2>

<ul class="comments">

<?php foreach ($comments as $comment): ?>
  
<li<?php
$title = '';
if (isset($comment->user) and $comment->user == $post->user) {
  echo ' class="op"';
  $title = ' <span class="title">' . tr('Post author') . '</span>';
}
?> id="comment<?php echo $comment->id; ?>">
<div class="comment-avatar">
<img src="http://1.gravatar.com/avatar/<?php
  if (!empty($comment->email))
    echo md5($comment->email);
  else
    echo md5($comment->ip);
?>?s=50&amp;d=monsterid&amp;r=G"
   alt="<?php echo h($comment->author); ?>"/>
</div>
<div class="comment">
<div class="author"><?php
  if (empty($comment->author)) {
    echo tr('Anonymous');
  }
  else {
    $website = $Html->cleanUrl($comment->website);
    if (empty($website))
      echo h($comment->author);
    else
      echo '<a href="' . $website . '">' . h($comment->author) . '</a>';
  }
  echo $title;
?>
<?php if (isset($comment->parent)): ?>
<span class="parent"><?php echo tr('In reply to %1', $Html->link(h($comment->parent->author), $comment->parent)); ?></span>
<?php endif; ?>
</div>
  <p><?php echo $comment->content; ?></p>
<div class="byline">
<?php
echo '<date datetime="' . date('c', $comment->createdAt) . '">'
      . sdate($comment->createdAt) . '</date>';
echo ' | ' . $Html->link(tr('Permalink'), $comment);
echo ' | <a href="#comment" class="reply">' . tr('Reply') . '</a>';
?>
</div>

<?php if (isset($newComment) and $newComment->parentId == $comment->id): ?>
<?php echo $this->embed('comments/add.html'); ?>
<?php $replying = true; ?>
<?php endif; ?>

</div>
</li>
<?php endforeach; ?>

</ul>

<div class="pagination">
  <?php if (!$Pagination->isFirst())
    echo $Html->link('&#8592; Back ', $Pagination->prevLink('comments')); ?>
  <div class="right">
    <?php if (!$Pagination->isLast())
    echo $Html->link('More comments &#8594;', $Pagination->nextLink('comments')); ?>
  </div>
</div>
<?php
endif;
?>

<?php if ($post->commenting) : ?>

<div id="comment-form-container">

<?php if (!$replying): ?>
<?php echo $this->embed('comments/add.html'); ?>
<?php endif; ?>

</div>


<?php endif; ?>

