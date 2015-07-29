<?php
$this->import('jquery.js', 'jivoo/jtk/data-table.js');
?>
<div class="data-table">
<div class="toolbar">
<?php echo $Form->form(array(), array(
  'method' => 'get',
  'id' => $object->id . 'filter',
)); ?>

<?php echo $Form->hidden('sortBy', array('value' => $object->sortBy)); ?>
<?php echo $Form->hidden('order', array('value' => $object->sortBy->descending ? 'desc' : 'asc')); ?>

<?php echo $Form->text('filter', array('placeholder' => tr('Filter'))); ?>

<?php echo $Jtk->iconButton(tr('Search'), 'type=submit icon=search'); ?>

<?php if (count($object->filters) == 0): ?>
<button>Reset</button>
<?php else: ?>
<div class="dropdown">
<a href="#">View</a>
<?php
$menu = $Jtk->Menu;
$object->filters->prependNew(tr('All'), '');
foreach ($object->filters as $filter)
  $menu->appendAction(h($filter->label))->setRoute($filter);
echo $menu();
?>
</div>

<?php endif; ?>

<?php echo $Form->end(); ?>

</div>

<?php echo $Form->hiddenToken(); ?>

<div class="table-operations">

<?php if (isset($object->addRoute)): ?>
<?php echo $Jtk->button(h('Add'), array('icon=plus context=primary', 'route' => $object->addRoute)); ?>
<?php endif; ?>

<?php if (count($object->bulkActions) > 0): ?>
<div class="dropdown dropdown-actions">
<a href="#"><?php echo tr('With selection'); ?></a>
<ul>
<?php foreach($object->bulkActions as $action): ?>
<li><?php echo $Jtk->button(h($action->label), array(
  'icon' => $action->icon,
  'data' => array(
    'action' => $this->link($this->mergeRoutes($action->route, array('$id'))),
    'method' => $action->method,
    'data' => json_encode($action->data),
    'confirm' => $action->confirmation
  )
)); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if (count($object->sortOptions) > 0): ?>

<div class="dropdown">
<a href="#"><?php echo tr('Sort by'); ?></a>
<ul>
<?php foreach ($object->sortOptions as $column): ?>
<?php if ($column->selected and $column->descending): ?>
<li class="selected selected-desc"><?php echo $Html->link(
  $column->label,
  array('query' => array('order' => 'asc'), 'mergeQuery' => true)
); ?></li>
<?php elseif ($column->selected and !$column->descending): ?>
<li class="selected selected-asc"><?php echo $Html->link(
  $column->label,
  array('query' => array('order' => 'desc'), 'mergeQuery' => true)
); ?></li>
<?php else: ?>
<li><?php echo $Html->link(
  $column->label,
  array('query' => array('sortBy' => $column->field, 'order' => 'asc'), 'mergeQuery' => true)
); ?></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
</div>

<?php echo $this->embed('jivoo/jtk/table/pagination.html', array('Pagination' => $Pagination)); ?>

<table<?php if (isset($object->density))
  echo ' class="density-' . $object->density . '"'; ?>>
<thead>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($object->columns as $column): ?>

<th<?php
if ($column->primary)
  echo ' class="main"';
else
  echo ' class="non-essential"';
if (isset($column->size)) {
  if (is_int($column->size))
    $column->size = $column->size . 'px';
  echo ' style="width: ' . $column->size . ';"';
}
else if (!$column->primary) {
  echo ' style="width: 15%;"';
}
?>
 scope="col">
<?php $sortOption = $object->sortOptions->find(function($b) use($column) {
  return $b->field === $column->field;
}); ?>
<?php if (isset($sortOption)): ?>
<?php if ($sortOption->selected and $sortOption->descending): ?>
<?php echo $Html->link(
  $column->label,
  array('query' => array('order' => 'asc'), 'mergeQuery' => true),
  array('class' => 'selected-desc')
); ?>
<?php elseif ($sortOption->selected and !$sortOption->descending): ?>
<?php echo $Html->link(
  $column->label,
  array('query' => array('order' => 'desc'), 'mergeQuery' => true),
  array('class' => 'selected-asc')
); ?>
<?php else: ?>
<?php echo $Html->link(
  $column->label,
  array('query' => array('sortBy' => $column->field, 'order' => 'asc'), 'mergeQuery' => true)
); ?>
<?php endif; ?>
<?php else: ?>
<?php echo h($column->label); ?>
<?php endif; ?>
</th>
<?php endforeach; ?>
<th class="actions non-essential" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</thead>
<tbody>

<?php foreach ($object as $row): ?>
<?php echo $this->embed('jivoo/jtk/table/row.html', array('row' => $row)); ?>
<?php endforeach; ?>

<?php foreach ($object->selection as $record): ?>
<?php echo $this->embed(
  'jivoo/jtk/table/row.html',
  array('row' => $object->createRow($record))
); ?>
<?php endforeach; ?>

</tbody>
<tfoot>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($object->columns as $column): ?>
<th<?php
if ($column->primary)
  echo ' class="main"';
else
  echo ' class="non-essential"';
?>
 scope="col">
<?php echo h($column->label); ?>
</th>
<?php endforeach; ?>
<th class="actions non-essential" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</tfoot>
</table>

<?php echo $this->embed('jivoo/jtk/table/pagination.html', array('Pagination' => $Pagination)); ?>

<div class="table-settings-box">
<?php echo $Form->formFor($tableSettings); ?>
<div class="field">
<?php echo $Form->label('perPage', tr('Rows per page')); ?>
<?php echo $Form->selectOf('perPage', array(
  '5' => '5',
  '10' => '10',
  '20' => '20',
  '50' => '50',
  '100' => '100'
)); ?>
</div>
<div class="field">
<?php echo $Form->label('density', tr('Row density')); ?>
<?php echo $Form->selectOf('density', array(
  'high' => tr('Compact'),
  'medium' => tr('Cozy'),
  'low' => tr('Comfortable'),
)); ?>
</div>
<div class="buttons">
<?php echo $Form->submit(tr('Save')); ?>
</div>
<?php echo $Form->end(); ?>
</div>

<div class="table-selection">
<?php echo tr('%1 items selected', '<span class="selection-count">0</span>'); ?>

<a href="#" class="select-all">(<?php echo tr('Select all %1 items', $Pagination->getCount()); ?>)</a>
</div>

</div>
