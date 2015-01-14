<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $posts,
  'columns' => array('title', 'author', 'status', 'updated'),
  'labels' => array(
    'author' => tr('Author'),
  ),
  'addRoute' => 'add',
  'sortOptions' => array('title', 'status', 'updated', 'created', 'published'),
  'defaultSortBy' => 'updated',
  'defaultDescending' => true,
  'filters' => array(
    tr('Published') => 'status=published',
    tr('Pending review') => 'status=pending',
    tr('Draft') => 'status=draft'
  ),
  'actions' => array(
    new TableAction(tr('Edit'), 'Admin::Posts::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('View'), 'Posts::view',
      'screen', array(), 'get'),
    'publish' => new TableAction(tr('Publish'), 'Admin::Posts::edit',
      'eye', array('Post' => array('status' => 'published'))),
    'unpublish' => new TableAction(tr('Unpublish'), 'Admin::Posts::edit',
      'eye-blocked', array('Post' => array('status' => 'pending'))),
    new TableAction(tr('Delete'), 'Admin::Posts::delete',
      'remove', array(), 'post', tr('Delete selected post?')),
  ),
  'bulkActions' => array(
    new TableAction(tr('Edit'), 'Admin::Posts::edit',
      'pencil', array(), 'get'),
    new TableAction(tr('Publish'), 'Admin::Posts::edit',
      'eye', array('Post' => array('status' => 'published'))),
    new TableAction(tr('Unpublish'), 'Admin::Posts::edit',
      'eye-blocked', array('Post' => array('status' => 'pending'))),
    new TableAction(tr('Delete'), 'Admin::Posts::delete',
      'remove', array(), 'post', tr('Delete selected posts?')),
  )
));
foreach ($widget as $item) {
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Html->link($item->title, $item->action('edit')),
      isset($item->user) ? $Html->link($item->user->username, $item->user) : '',
      $item->status,
      ldate($item->updated)
    ),
    'removeActions' => array($item->status == 'published' ? 'publish' : 'unpublish')
  ));
}
echo $widget->end();
?>
