<?php $this->extend('admin/layout.html'); ?>

<?php echo $Widget->widget('RecordIndex', array(
  'model' => $comments,
  'defaultSortBy' => 'createdAt',
  'defaultDescending' => true,
  'filters' => array(
    tr('Approved') => 'status=approved',
    tr('Pending review') => 'status=pending',
    tr('Spam') => 'status=spam'
  ),
  'columns' => array(
    new RecordIndexColumn('content', null, true),
    new RecordIndexRecordColumn('post', tr('Post'), false, 'title', 'view'),
    new RecordIndexColumn('status'),
    new RecordIndexDateColumn('createdAt'),
  ),
  'defaultAction' => 'edit',
  'bulkActions' => array(
    new RecordIndexBulkAction(tr('Edit'), 'Admin::Comments::bulkEdit', 'pencil'),
    new RecordIndexBulkAction(tr('Approve'), 'Admin::Comments::bulkEdit', 'checkmark'),
    new RecordIndexBulkAction(tr('Unapprove'), 'Admin::Comments::bulkEdit', 'close'),
    new RecordIndexBulkAction(tr('Spam'), 'Admin::Comments::bulkEdit', 'thumbs-up2'),
    new RecordIndexBulkAction(tr('Not spam'), 'Admin::Comments::bulkEdit', 'thumbs-up'),
    new RecordIndexBulkAction(tr('Delete'), 'Admin::Comments::bulkEdit', 'remove'),
  ),
  'recordActions' => array(
    new RecordIndexAction(tr('Edit'), 'edit', 'pencil'),
    new RecordIndexAction(tr('View'), 'view', 'screen'),
    new RecordIndexAction(tr('Approve'), 'approve', 'checkmark'),
    new RecordIndexAction(tr('Spam'), 'spam', 'thumbs-up2'),
    new RecordIndexAction(tr('Delete'), 'delete', 'remove'),
  ),
)); ?>
