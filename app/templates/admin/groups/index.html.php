<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $groups,
  'columns' => array('name', 'title', 'users'),
  'labels' => array(
    'users' => tr('Users'),
  ),
  'addRoute' => 'add',
  'sortOptions' => array('name', 'title'),
  'defaultSortBy' => 'name',
  'defaultDescending' => false,
  'actions' => array(
    new TableAction(tr('Edit'), 'Admin::Groups::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Delete'), 'Admin::Groups::delete',
      'remove', array(), 'post', tr('Delete selected group?')),
  ),
  'bulkActions' => array(
    new TableAction(tr('Edit'), 'Admin::Groups::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Delete'), 'Admin::Groups::delete',
      'remove', array(), 'post', tr('Delete selected groups?')),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Html->link($item->name, $item->action('edit')),
      $item->title,
      $item->users->count(),
    ),
  ));
}
echo $widget->end();
?>