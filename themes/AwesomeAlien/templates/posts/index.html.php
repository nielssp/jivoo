<?php $this->extend('layout.html'); ?>

<?php if (!$Pagination->isFirst()) : ?>
<div class="pagination">
  <?php if (!$Pagination->isLast())
    echo $Html->link('&#8592; Older posts', $Pagination->nextLink()); ?>
  <div class="right">
    <?php echo $Html->link('Newer posts &#8594', $Pagination->prevLink()); ?>
  </div>
</div>
<?php endif; ?>

<?php foreach ($posts as $post) : ?>

<div class="post">
  <h1>
    <?php echo $Html->link(h($post->title), $post); ?>
  </h1>
<?php echo $post->content; ?>

<div class="byline"><?php echo tr('Posted on %1', fdate($post->createdAt)) ?>
 | <a href="<?php echo $this->link($post);
  if (!isset($post->comments))
    echo '#comment">' . tr('Leave a comment');
  else
    echo '#comments">' . tn('%1 comments', '%1 comment', $post->comments->where('status = "approved"')->count());
?></a></div>
</div>
<?php endforeach; ?>

<div class="pagination">
  <?php if (!$Pagination->isLast())
  echo $Html->link('&#8592; Older posts', $Pagination->nextLink()); ?>
  <div class="right">
    <?php if (!$Pagination->isFirst())
  echo $Html->link('Newer posts &#8594;', $Pagination->prevLink()); ?>
  </div>
</div>
