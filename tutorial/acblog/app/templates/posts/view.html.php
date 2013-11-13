<?php
// app/templates/posts/view.html.php
$this->extend('layout.html');
?>
<h2><?php echo $post->title; ?></h2>

<?php echo $post->content; ?>
