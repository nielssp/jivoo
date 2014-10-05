<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $users,
  'columns' => array('username', 'group', 'email', 'created'),
  'labels' => array(
    'group' => tr('Group'),
  ),
  'addRoute' => 'add',
  'sortOptions' => array('username', 'email', 'updated', 'created'),
  'defaultSortBy' => 'created',
  'defaultDescending' => true,
  'actions' => array(
    new TableAction(tr('Edit'), 'Admin::Users::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Delete'), 'Admin::Users::delete',
      'remove', array(), 'post', tr('Delete selected user?')),
  ),
  'bulkActions' => array(
    new TableAction(tr('Edit'), 'Admin::Users::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Delete'), 'Admin::Users::delete',
      'remove', array(), 'post', tr('Delete selected users?')),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Html->link($item->username, $item->action('edit')),
      $Html->link($item->group->name, $item->group),
      $item->email,
      ldate($item->created)
    ),
  ));
}
echo $widget->end();
?>