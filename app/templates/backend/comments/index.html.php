<?php
$this->extend('backend/index-layout.html');
?>
<?php if (count($comments) < 1) : ?>
<div class="center">
	<?php echo tr('No comments matched your search criteria.') ?>
</div>
<?php endif; ?>
<?php
$this->first = true;
foreach ($comments as $this->comment) {
  $this->embed('comments/comment.html');
  if ($this->first) {
    $this->first = false;
  }
}
?>
