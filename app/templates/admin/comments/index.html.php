<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('DataTable', array(
  'model' => $comments,
  'columns' => array('content', 'post', 'status', 'created'),
  'labels' => array(
    'post' => tr('Post'),
  ),
  'addRoute' => 'add',
  'sortOptions' => array('status', 'updated', 'created'),
  'defaultSortBy' => 'created',
  'defaultDescending' => true,
  'filters' => array(
    tr('Approved') => 'status=approved',
    tr('Pending review') => 'status=pending',
    tr('Spam') => 'status=spam'
  ),
  'actions' => array(
    new TableAction(tr('Edit'), 'Admin::Comments::edit', 
      'pencil', array(), 'get'),
    'approve' => new TableAction(tr('Approve'), 'Admin::Comments::edit',
      'checkmark', array('Comment' => array('status' => 'approved'))),
    'unapprove' => new TableAction(tr('Unapprove'), 'Admin::Comments::edit',
      'close', array('Comment' => array('status' => 'pending'))),
    'spam' => new TableAction(tr('Spam'), 'Admin::Comments::edit',
      'thumbs-up2', array('Comment' => array('status' => 'spam'))),
    new TableAction(tr('Delete'), 'Admin::Comments::delete',
      'remove', array(), 'post', tr('Delete selected comment?')),
  ),
  'bulkActions' => array(
    new TableAction(tr('Edit'), 'Admin::Comments::edit', 
      'pencil', array(), 'get'),
    new TableAction(tr('Approve'), 'Admin::Comments::edit',
      'checkmark', array('Comment' => array('status' => 'approved'))),
    new TableAction(tr('Unapprove'), 'Admin::Comments::edit',
      'close', array('Comment' => array('status' => 'pending'))),
    new TableAction(tr('Spam'), 'Admin::Comments::edit',
      'thumbs-up2', array('Comment' => array('status' => 'spam'))),
    new TableAction(tr('Delete'), 'Admin::Comments::delete',
      'remove', array(), 'post', tr('Delete selected comments?')),
  )
));
foreach ($widget as $item) {
  $removeActions = null;
  switch ($item->status) {
    case 'approved':
      $removeActions = array('approve');
      break;
    case 'spam':
      $removeActions = array('spam', 'unapprove');
      break;
    case 'pending':
      $removeActions = array('unapprove');
      break;
  }
  echo $widget->handle($item, array(
    'id' => $item->id,
    'cells' => array(
      $Format->html($item, 'content', array(
        'stripAll' => true,
        'maxLength' => 150,
        'append' => isset($item->post) ? $Html->link('[...]', $item) : '[...]'
      )),
      isset($item->post) ? $Html->link($item->post->title, $item) : '',
      $item->status,
      ldate($item->created)
    ),
    'removeActions' => $removeActions
  ));
}
echo $widget->end();
?>