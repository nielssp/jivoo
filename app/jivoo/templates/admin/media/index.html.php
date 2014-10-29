<?php $this->extend('admin/layout.html'); ?>

<?php
echo $Widget->widget('BasicDataTable', array(
  'model' => $model,
  'records' => $files,
  'columns' => array('name', 'size', 'modified', 'created'),
  'primaryColumn' => 'name',
  'addRoute' => 'add',
  'sortOptions' => array('name', 'size', 'modified', 'created'),
  'defaultSortBy' => 'name',
));
?>