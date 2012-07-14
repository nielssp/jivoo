<?php
/* 
 * Template for blog post listing
 */

// Render the header
$this->render('header.html');
?>

<?php if (!$Pagination->isFirst()): ?>
<div class="pagination">
  <?php if (!$Pagination->isLast()) echo $Html->link('&#8592; Older posts', $Pagination->nextLink()); ?>
  <div class="right">
    <?php echo $Html->link('Newer posts &#8594', $Pagination->prevLink()); ?>
  </div>
</div>
<?php endif; ?>

<?php foreach ($posts as $post): ?>

<div class="post">
  <h1>
    <?php echo $Html->link(h($post->title), $post); ?>
  </h1>

<?php echo $post->content; ?>

<div class="byline"><?php echo tr('Posted on %1', $post->formatDate())?>
 | <a href="<?php echo $this->link($post);
if ($post->comments == 0)
  echo '#comment">' . tr('Leave a comment');
else
  echo '#comments">' . trn('%1 comment', '%1 comments', $post->comments);
?></a></div>
</div>

<?php endforeach; ?>

<div class="pagination">
  <?php if (!$Pagination->isLast()) echo $Html->link('&#8592; Older posts', $Pagination->nextLink()); ?>
  <div class="right">
    <?php if (!$Pagination->isFirst()) echo $Html->link('Newer posts &#8594;', $Pagination->prevLink()); ?>
  </div>
</div>

<?php
// Render the footer
$this->render('footer.html');
?>
