<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $tags,
  'columns' => array('tag', 'name', 'posts'),
  'labels' => array(
    'posts' => tr('Posts'),
  ),
  'addRoute' => 'add',
  'sortOptions' => array('tag', 'name'),
  'defaultSortBy' => 'tag',
  'defaultDescending' => false,
  'actions' => array(
    new TableAction(tr('Edit'), 'Admin::Tags::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Delete'), 'Admin::Tags::delete',
      'remove', array(), 'post', tr('Delete selected tags?')),
  ),
  'bulkActions' => array(
    new TableAction(tr('Edit'), 'Admin::Tags::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Delete'), 'Admin::Tags::delete',
      'remove', array(), 'post', tr('Delete selected tags?')),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Html->link($item->tag, $item->action('edit')),
      $item->name,
      $Html->link($item->posts->count(), $item->action('view')),
    ),
  ));
}
echo $widget->end();
?>