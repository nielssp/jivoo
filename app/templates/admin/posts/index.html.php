<?php $this->extend('admin/layout.html'); ?>

<?php echo $Widget->widget('RecordIndex', array(
  'model' => $posts,
  'defaultSortBy' => 'createdAt',
  'defaultDescending' => true,
  'filters' => array(
    tr('Published') => 'status=published',
    tr('Pending review') => 'status=pending',
    tr('Draft') => 'status=draft'
  ),
  'columns' => array(
    new RecordIndexColumn('title', null, true),
    new RecordIndexRecordColumn('user', tr('Author'), false, 'username', 'view'),
    new RecordIndexColumn('status'),
    new RecordIndexDateColumn('createdAt'),
  ),
  'defaultAction' => 'edit',
  'bulkActions' => array(
    new RecordIndexBulkAction(tr('Edit'), 'Admin::Posts::bulkEdit', 'pencil'),
    new RecordIndexBulkAction(tr('Publish'), 'Admin::Posts::bulkEdit', 'eye'),
    new RecordIndexBulkAction(tr('Unpublish'), 'Admin::Posts::bulkEdit', 'eye-blocked'),
    new RecordIndexBulkAction(tr('Delete'), 'Admin::Posts::bulkEdit', 'remove'),
  ),
  'recordActions' => array(
    new RecordIndexAction(tr('Edit'), 'edit', 'pencil'),
    new RecordIndexAction(tr('View'), 'view', 'screen'),
    new RecordIndexAction(tr('Publish'), 'publish', 'eye'),
    new RecordIndexAction(tr('Unpublish'), 'unpublish', 'eye-blocked'),
    new RecordIndexAction(tr('Delete'), 'delete', 'remove'),
  ),
)); ?>
