<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $users,
  'columns' => array('username', 'group', 'email', 'createdAt'),
  'labels' => array(
    'group' => tr('Group'),
  ),
  'sortOptions' => array('username', 'email', 'updatedAt', 'createdAt'),
  'defaultSortBy' => 'createdAt',
  'defaultDescending' => true,
  'actions' => array(
    new RowAction(tr('Edit'), 'edit', 'pencil'),
    new RowAction(tr('Delete'), 'delete', 'remove'),
  ),
  'bulkActions' => array(
    new BulkAction(tr('Edit'), 'Admin::Users::bulkEdit', 'pencil'),
    new BulkAction(tr('Delete'), 'Admin::Users::bulkEdit', 'remove'),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Html->link($item->username, $item->action('edit')),
      $Html->link($item->group->name, $item->group),
      $item->email,
      ldate($item->createdAt)
    ),
  ));
}
echo $widget->end();
?>