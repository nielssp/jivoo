<?php
$this->script('jquery.js'); 
$this->script('widgets/data-table.js');
?>
<div class="data-table">
<div class="toolbar">
<?php echo $Form->form(array(), array(
  'method' => 'get',
  'id' => $options['id'] . 'filter',
)); ?>

<?php echo $Form->hidden('sortBy', array('value' => $sortBy)); ?>
<?php echo $Form->hidden('order', array('value' => $descending ? 'desc' : 'asc')); ?>

<?php echo $Form->text('filter', array('placeholder' => tr('Filter'))); ?>

<?php echo $Icon->button(tr('Search'), 'search'); ?>

<?php if (count($options['filters']) == 0): ?>
<button>Reset</button>
<?php else: ?>
<div class="dropdown">
<a href="#">View</a>
<ul>
<li><?php echo $Html->link(tr('All'), array(
  'query' => array('filter' => null),
  'mergeQuery' => true
)); ?></li>
<?php foreach ($options['filters'] as $label => $filter): ?>
<li><?php echo $Html->link(h($label), array(
  'query' => array('filter' => $filter),
  'mergeQuery' => true
)); ?></li>
<?php endforeach; ?>
</ul>
</div>

<?php endif; ?>

<?php echo $Form->end(); ?>

</div>

<?php echo $Form->hiddenToken(); ?>

<div class="table-operations">

<?php if (isset($options['addRoute'])): ?>
<?php echo $Icon->link(h('Add'), $options['addRoute'], 'plus', null, array('class' => 'button')); ?>
<?php endif; ?>

<?php if (count($options['bulkActions']) > 0): ?>
<div class="dropdown dropdown-actions">
<a href="#"><?php echo tr('With selection'); ?></a>
<ul>
<?php foreach($options['bulkActions'] as $bulkAction): ?>
<li><?php echo $Icon->button(h($bulkAction->label), $bulkAction->icon, array(
  'data' => array(
    'action' => $this->link($this->mergeRoutes($bulkAction->route, array('?'))),
    'method' => $bulkAction->method,
    'data' => json_encode($bulkAction->data),
    'confirm' => $bulkAction->confirmation
  )
)); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if (count($sortOptions) > 0): ?>

<div class="dropdown">
<a href="#"><?php echo tr('Sort by'); ?></a>
<ul>
<?php foreach ($sortOptions as $column): ?>
<?php if ($sortBy == $column and $descending): ?>
<li class="selected selected-desc"><?php echo $Html->link(
  $labels[$column],
  array('query' => array('order' => 'asc'), 'mergeQuery' => true)
); ?></li>
<?php elseif ($sortBy == $column and !$descending): ?>
<li class="selected selected-asc"><?php echo $Html->link(
  $labels[$column],
  array('query' => array('order' => 'desc'), 'mergeQuery' => true)
); ?></li>
<?php else: ?>
<li><?php echo $Html->link(
  $labels[$column],
  array('query' => array('sortBy' => $column, 'order' => 'asc'), 'mergeQuery' => true)
); ?></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
</div>

<?php echo $this->embed('widgets/data-table-pagination.html', array('Pagination' => $Pagination)); ?>

<table>
<thead>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($columns as $column): ?>

<th<?php if ($column == $primaryColumn) echo ' class="primary"'; ?>
 scope="col">
<?php if (in_array($column, $sortOptions)): ?>
<?php if ($sortBy == $column and $descending): ?>
<?php echo $Html->link(
  $labels[$column],
  array('query' => array('order' => 'asc'), 'mergeQuery' => true),
  array('class' => 'selected-desc')
); ?>
<?php elseif ($sortBy == $column and !$descending): ?>
<?php echo $Html->link(
  $labels[$column],
  array('query' => array('order' => 'desc'), 'mergeQuery' => true),
  array('class' => 'selected-asc')
); ?>
<?php else: ?>
<?php echo $Html->link(
  $labels[$column],
  array('query' => array('sortBy' => $column, 'order' => 'asc'), 'mergeQuery' => true)
); ?>
<?php endif; ?>
<?php else: ?>
<?php echo $labels[$column]; ?>
<?php endif; ?>
</th>
<?php endforeach; ?>
<th class="actions" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</thead>
<tbody>

<?php echo $this->block('widget-content'); ?>

</tbody>
<tfoot>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($columns as $column): ?>
<th<?php if ($column == $primaryColumn) echo ' class="primary"'; ?>
 scope="col">
<?php echo h($labels[$column]); ?>
</th>
<?php endforeach; ?>
<th class="actions" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</tfoot>
</table>

<div class="table-operations">
<span class="selection-count">0</span> items selected
<a href="#" class="select-all">(Select all <?php echo $Pagination->getCount(); ?> items)</a>
</div>

<?php echo $this->embed('widgets/data-table-pagination.html', array('Pagination' => $Pagination)); ?>

</div>
