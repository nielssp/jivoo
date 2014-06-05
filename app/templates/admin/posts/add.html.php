<?php $this->extend('admin/layout.html'); ?>

<?php echo $Widget->widget('Publish', array(
  'record' => $post
)); ?>