<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $links,
  'columns' => array('title', 'type', 'menu'),
  'sortOptions' => array('menu', 'type', 'title'),
  'defaultSortBy' => 'title',
  'defaultDescending' => true,
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Html->link($item->title, $item),
      $item->type,
      $item->menu
    ),
  ));
}
echo $widget->end();
?>