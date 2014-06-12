<div class="toolbar">
<?php echo $Form->form(array(), array(
  'method' => 'get',
  'id' => $options['id'] . 'filter',
)); ?>

<?php echo $Form->text('filter', array('placeholder' => tr('Filter'))); ?>

<?php echo $Icon->button(tr('Search'), 'search'); ?>

<?php if (count($options['filters']) == 0): ?>
<button>Reset</button>
<?php else: ?>
<div class="dropdown">
<a href="#">View</a>
<ul>
<li class="selected"><a href="#">All</a></li>
<?php foreach ($options['filters'] as $label => $filter): ?>
<li><a href="#"><?php echo $label; ?></a></li>
<?php endforeach; ?>
</ul>
</div>

<?php endif; ?>

<?php echo $Form->end(); ?>

</div>

<div class="table-operations">

<?php if (count($options['bulkActions']) > 0): ?>
<div class="dropdown dropdown-actions">
<a href="#"><?php echo tr('With selection'); ?></a>
<ul>
<?php foreach($options['bulkActions'] as $bulkAction): ?>
<li><?php echo $Icon->button(h($bulkAction->label), $bulkAction->icon); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if (count($options['sortBy']) > 0): ?>
<div class="dropdown">
<a href="#"><?php echo tr('Sort by'); ?></a>
<ul>
<?php foreach ($options['sortBy'] as $column): ?>
<li><a href="#"><?php echo $column->getLabel($model); ?></a></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
</div>

<?php echo $this->embed('widgets/record-pagination.html', array('Pagination' => $Pagination)); ?>

<table>
<thead>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($options['columns'] as $column): ?>
<th<?php if ($column->primary) echo ' class="primary"'; ?>
 scope="col">
<?php echo h($column->getLabel($model)); ?>
</th>
<?php endforeach; ?>
<th class="actions" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($records as $record): ?>
<?php $this->embed($options['recordTemplate'], array(
  'record' => $record,
  'model' => $model,
  'columns' => $options['columns'],
  'actions' => $options['recordActions'],
  'defaultAction' => $options['defaultAction']
)); ?>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
<th class="selection" scope="col">
<label><input type="checkbox" /></label>
</th>
<?php foreach ($options['columns'] as $column): ?>
<th<?php if ($column->primary) echo ' class="primary"'; ?>
 scope="col">
<?php echo h($column->getLabel($model)); ?>
</th>
<?php endforeach; ?>
<th class="actions" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</tfoot>
</table>

<?php echo $this->embed('widgets/record-pagination.html', array('Pagination' => $Pagination)); ?>
