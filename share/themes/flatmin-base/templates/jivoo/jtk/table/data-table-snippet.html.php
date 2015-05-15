<?php
$this->import('jquery.js', 'widgets/data-table.js');
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

<?php echo $Icon->button(tr('Search'), 'search'); ?>

<?php if (count($object->filters) == 0): ?>
<button>Reset</button>
<?php else: ?>
<div class="dropdown">
<a href="#">View</a>
<?php
$menu = $Jtk->Menu;
$menu->appendAction(tr('All'))->setRoute(array(
  'query' => array('filter' => null),
  'mergeQuery' => true
));
foreach ($object->filters as $filter) {
  $menu->appendAction(h($filter->label))->setRoute(array(
    'query' => array('filter' => $filter->filter),
    'mergeQuery' => true
  ));
}
echo $menu();
?>
</div>

<?php endif; ?>

<?php echo $Form->end(); ?>

</div>

<?php echo $Form->hiddenToken(); ?>

<div class="table-operations">

<?php if (isset($object->addRoute)): ?>
<?php echo $Icon->link(h('Add'), $object->addRoute, 'plus', null, array('class' => 'button')); ?>
<?php endif; ?>

<?php if (count($object->bulkActions) > 0): ?>
<div class="dropdown dropdown-actions">
<a href="#"><?php echo tr('With selection'); ?></a>
<ul>
<?php foreach($object->bulkActions as $action): ?>
<li><?php echo $Icon->button(h($action->label), $action->icon, array(
  'data' => array(
    'action' => $this->link($this->mergeRoutes($action->route, array('?'))),
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

<table>
<thead>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($object->columns as $column): ?>

<th<?php
if ($column->primary)
  echo ' class="primary"';
else
  echo ' class="non-essential"';
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
  echo ' class="primary"';
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

<div class="table-operations">
<span class="selection-count">0</span> items selected
<a href="#" class="select-all">(Select all <?php echo $Pagination->getCount(); ?> items)</a>
</div>

<?php echo $this->embed('jivoo/jtk/table/pagination.html', array('Pagination' => $Pagination)); ?>

</div>
