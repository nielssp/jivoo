<?php $this->extend('admin/layout.html'); ?>


<?php foreach ($themes as $theme): ?>
<?php echo h($theme->name); ?>
<?php endforeach; ?>