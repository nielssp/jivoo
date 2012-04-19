<?php
/*
 * Template for blog post listing
 */

// Render the header
$this->renderTemplate('header.html');

?>


<?php foreach ($posts as $post): ?>

<h2>
  <?php $this->linkTo($post, $post->title); ?>
</h2>

<p>
  Published <?php echo $post->formatDate(); ?>
  @ <?php echo $post->formatTime(); ?>
</p>

<?php echo $post->content; ?>

<?php endforeach; ?>



<?php
// Render the footer
$this->renderTemplate('footer.html');
?>