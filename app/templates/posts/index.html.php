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



<?php
// Render the footer
$this->render('footer.html');
?>
