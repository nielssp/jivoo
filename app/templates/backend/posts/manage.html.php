<?php
$this->extend('backend/layout.html');
$this->embed('backend/pagination.html');
$this->embed('backend/bulk-actions.html');
?>

      <div class="section light_section">
        <div class="container">
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
        </div>
      </div>
<?php
$this->embed('backend/bulk-actions.html');
?>

