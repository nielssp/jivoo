<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $pages,
  'columns' => array('title', 'name', 'published', 'updatedAt'),
  'sortOptions' => array('title', 'name', 'published', 'updatedAt', 'createdAt'),
  'defaultSortBy' => 'updatedAt',
  'defaultDescending' => true,
  'primaryAction' => 'edit',
  'filters' => array(
    tr('Published') => 'published=true',
    tr('Draft') => 'published=false'
  ),
  'actions' => array(
    new RowAction(tr('Edit'), 'edit', 'pencil'),
    new RowAction(tr('View'), 'view', 'screen'),
    'publish' => new RowAction(tr('Publish'), 'publish', 'eye'),
    'unpublish' => new RowAction(tr('Unpublish'), 'unpublish', 'eye-blocked'),
    new RowAction(tr('Delete'), 'delete', 'remove'),
  ),
  'bulkActions' => array(
    new BulkAction(tr('Edit'), 'Admin::Pages::bulkEdit', 'pencil'),
    new BulkAction(tr('Publish'), 'Admin::Pages::bulkEdit', 'eye'),
    new BulkAction(tr('Unpublish'), 'Admin::Pages::bulkEdit', 'eye-blocked'),
    new BulkAction(tr('Delete'), 'Admin::Pages::bulkEdit', 'remove'),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'removeActions' => array($item->published ? 'publish' : 'unpublish')
  ));
}
echo $widget->end();
?>