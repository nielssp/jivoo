<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $pages,
  'columns' => array('title', 'name', 'published', 'updatedAt'),
  'sortOptions' => array('title', 'name', 'published', 'updatedAt', 'createdAt'),
  'defaultSortBy' => 'name',
  'primaryAction' => 'edit',
  'addRoute' => 'add',
  'filters' => array(
    tr('Published') => 'published=true',
    tr('Draft') => 'published=false'
  ),
  'actions' => array(
    new TableAction(tr('Edit'), 'Admin::Pages::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('View'), 'Pages::view',
      'screen', array(), 'get'),
    'publish' => new TableAction(tr('Publish'), 'Admin::Pages::edit',
      'eye', array('Page' => array('published' => true))),
    'unpublish' => new TableAction(tr('Unpublish'), 'Admin::Pages::edit',
      'eye-blocked', array('Page' => array('published' => false))),
    new TableAction(tr('Delete'), 'Admin::Pages::delete',
      'remove', array(), 'post', tr('Delete selected page')),
  ),
  'bulkActions' => array(
    new TableAction(tr('Edit'), 'Admin::Pages::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Publish'), 'Admin::Pages::edit',
      'eye', array('Page' => array('published' => true))),
    new TableAction(tr('Unpublish'), 'Admin::Pages::edit',
      'eye-blocked', array('Page' => array('published' => false))),
    new TableAction(tr('Delete'), 'Admin::Pages::delete',
      'remove', array(), 'post', tr('Delete selected pages')),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'removeActions' => array($item->published ? 'publish' : 'unpublish')
  ));
}
echo $widget->end();
?>