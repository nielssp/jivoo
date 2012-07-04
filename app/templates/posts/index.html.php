<?php
/*
 * Template for blog post listing
 */

// Render the header
$this->render('header.html');
?>

<?php foreach ($posts as $post): ?>

<h2>
  <?php echo $Html->link(h($post->title), $post); ?>
</h2>

<p>
  Published <?php echo $post->formatDate(); ?>
  @ <?php echo $post->formatTime(); ?>
</p>

<?php echo $post->content; ?>

<?php endforeach; ?>

<p>
  <?php if (!$Pagination->isLast()) echo $Html->link('&#8592; Older', $Pagination->nextLink()); ?>
  Page <?php echo $Pagination->getPage(); ?>
  of <?php echo $Pagination->getPages(); ?>
  <?php if (!$Pagination->isFirst()) echo $Html->link('Newer &#8594;', $Pagination->prevLink()); ?>
</p>

<?php
// Render the footer
$this->render('footer.html');
?>
