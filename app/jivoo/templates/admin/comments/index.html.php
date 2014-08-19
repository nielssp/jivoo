<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $comments,
  'columns' => array('content', 'post', 'status', 'createdAt'),
  'labels' => array(
    'post' => tr('Post'),
  ),
  'addRoute' => 'add',
  'sortOptions' => array('status', 'updatedAt', 'createdAt'),
  'defaultSortBy' => 'createdAt',
  'defaultDescending' => true,
  'filters' => array(
    tr('Approved') => 'status=approved',
    tr('Pending review') => 'status=pending',
    tr('Spam') => 'status=spam'
  ),
  'actions' => array(
    new RowAction(tr('Edit'), 'edit', 'pencil'),
    new RowAction(tr('View'), 'view', 'screen'),
    'approve' => new RowAction(tr('Approve'), 'approve', 'checkmark'),
    'unapprove' => new RowAction(tr('Unapprove'), 'unapprove', 'close'),
    'spam' => new RowAction(tr('Spam'), 'spam', 'thumbs-up2'),
    'notSpam' => new RowAction(tr('Not spam'), 'notSpam', 'thumbs-up'),
    new RowAction(tr('Delete'), 'delete', 'remove'),
  ),
  'bulkActions' => array(
    new BulkAction(tr('Edit'), 'Admin::Comments::bulkEdit', 'pencil'),
    new BulkAction(tr('Approve'), 'Admin::Comments::bulkEdit', 'checkmark'),
    new BulkAction(tr('Unapprove'), 'Admin::Comments::bulkEdit', 'close'),
    new BulkAction(tr('Spam'), 'Admin::Comments::bulkEdit', 'thumbs-up2'),
    new BulkAction(tr('Not spam'), 'Admin::Comments::bulkEdit', 'thumbs-up'),
    new BulkAction(tr('Delete'), 'Admin::Comments::bulkEdit', 'remove'),
  )
));
foreach ($widget as $item) {
  $removeActions = null;
  switch ($item->status) {
    case 'approved':
      $removeActions = array('notSpam', 'approve');
      break;
    case 'spam':
      $removeActions = array('spam', 'unapprove');
      break;
    case 'pending':
      $removeActions = array('notSpam', 'unapprove');
      break;
  }
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $item->content,
      $Html->link($item->post->title, $item->post),
      $item->status,
      ldate($item->createdAt)
    ),
    'removeActions' => $removeActions
  ));
}
echo $widget->end();
?>