<?php
$this->extend('backend/index-layout.html');
?>

<?php if (count($posts) < 1) : ?>
<div class="center">
<?php echo tr('No posts matched your search criteria.') ?>
</div>
<?php endif; ?>

<?php
$this->first = true;
foreach ($posts as $this->post) {
  $this->embed('posts/post.html');
  if ($this->first) {
    $this->first = false;
  }
}
?>
