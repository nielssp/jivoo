<?php $this->extend('admin/layout.html'); ?>

<?php echo $Widget->widget('RecordIndex', array(
  'model' => $users,
  'columns' => array(
    new RecordIndexColumn('username', null, true),
    new RecordIndexRecordColumn('group', null, false, 'title', 'view'),
    new RecordIndexColumn('email'),
    new RecordIndexDateColumn('createdAt'),
  ),
  'defaultAction' => 'edit',
  'bulkActions' => array(
    new RecordIndexBulkAction(tr('Edit'), 'Admin::Posts::bulkEdit', 'pencil'),
    new RecordIndexBulkAction(tr('Delete'), 'Admin::Posts::bulkEdit', 'remove'),
  ),
  'recordActions' => array(
    new RecordIndexAction(tr('Edit'), 'edit', 'pencil'),
    new RecordIndexAction(tr('Delete'), 'delete', 'remove'),
  ),
)); ?>
