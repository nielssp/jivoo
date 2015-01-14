<?php $this->extend('admin/layout.html'); ?>

<?php
$widget = $Widget->begin('BasicDataTable', array(
  'model' => $model,
  'records' => $extensions,
  'columns' => array('name', 'enabled', 'canonicalName', 'version'),
  'primaryColumn' => 'name',
  'sortOptions' => array('name', 'enabled', 'canonicalName'),
  'defaultSortBy' => 'name',
  'actions' => array(
    'enable' => new TableAction(tr('Enable'), 'Admin::Extensions::enable',
      'checkmark', array(), 'post'),
    'disable' => new TableAction(tr('Disable'), 'Admin::Extensions::disable',
      'close', array(), 'post'),
    'configure' => new TableAction(tr('Configure'), 'Admin::Extensions::configure',
      'wrench', array(), 'get')
  ),
));
foreach ($widget as $item) {
  $hide = array($item->enabled ? 'enable' : 'disable');
  if ($item->enabled) {
    if (isset($item->configure))
      $hide = array('enable');
    else
      $hide = array('enable', 'configure');
  }
  else
    $hide = array('disable', 'configure');
  echo $widget->handle($item, array(
    'id' => $item->canonicalName,
    'removeActions' => $hide,
    'class' => $item->enabled ? null : 'warn',
    'cells' => array(
      $item->enabled && isset($item->configure)
        ? $Html->link($item->name, array('action' => 'configure', $item->canonicalName))
        : $item->name,
      $item->enabled ? tr('Yes') : tr('No'),
      $item->canonicalName,
      $item->version
    ),
  ));
}
echo $widget->end();
?>