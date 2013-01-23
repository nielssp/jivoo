<?php
// Render the header
$this->render('backend/header.html');
$this->render('backend/pagination.html');
$this->render('backend/bulk-actions.html');
?>

      <div class="section light_section">
        <div class="container">
<?php if (count($comments) < 1) : ?>
          <div class="center">
          <?php echo tr('No comments matched your search criteria.') ?>
          </div>
<?php endif; ?>
<?php
$this->first = true;
foreach ($comments as $this->comment) {
  $this->render('comments/comment.html');
  if ($this->first) {
    $this->first = false;
  }
}
?>
        </div>
      </div>
<?php
$this->render('backend/bulk-actions.html');
$this->render('backend/footer.html');
?>

