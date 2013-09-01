<?php
$this->extend('backend/layout.html');
$this->embed('backend/pagination.html');
$this->embed('backend/bulk-actions.html');
?>
<div class="section light_section">
<div class="container">
<?php echo $this->block('content'); ?>
</div>
</div>

<?php $this->embed('backend/bulk-actions.html'); ?>