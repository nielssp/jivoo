<?php $this->extend('admin/layout.html'); ?>

<?php echo $Widget->widget('RecordIndex', array(
  'model' => $posts,
  'primary' => 'title',
  'filters' => array(
    tr('Published') => array('status' => 'published'),
    tr('Pending review') => array('status' => 'pending'),
    tr('Draft') => array('status' => 'draft')
  ),
  'columns' => array('title', 'user', 'status', 'createdAt'),
  'defaultAction' => 'edit',
  'bulkActions' => array(
    array(
      'label' => tr('Edit'),
      'icon' => 'pencil',
      'route' => 'Admin::Posts::bulkEdit'
    ),
    array(
      'label' => tr('Publish'),
      'icon' => 'eye',
      'route' => 'Admin::Posts::bulkEdit'
    ),
    array(
      'label' => tr('Unpublish'),
      'icon' => 'eye-blocked',
      'route' => 'Admin::Posts::bulkEdit'
    ),
    array(
      'label' => tr('Delete'),
      'icon' => 'remove',
      'route' => 'Admin::Posts::bulkEdit'
    ),
  ),
  'record' => array(
    'template' => 'widget/post-record.html',
    'actions' => array(
      array(
        'label' => tr('Edit'),
        'icon' => 'pencil',
        'action' => 'edit'
      ),
      array(
        'label' => tr('View'),
        'icon' => 'screen',
        'action' => 'view'
      ),
      array(
        'label' => tr('Publish'),
        'icon' => 'eye',
        'action' => 'publish'
      ),
      array(
        'label' => tr('Unpublish'),
        'icon' => 'eye-blocked',
        'action' => 'unpublish'
      ),
      array(
        'label' => tr('Delete'),
        'icon' => 'remove2',
        'action' => 'delete'
      ),
    ),
  ),
)); ?>
