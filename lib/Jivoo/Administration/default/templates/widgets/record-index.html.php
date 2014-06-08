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
<li><?php echo $Icon->button($bulkAction['label'], $bulkAction['icon']); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if (count($options['sortBy']) > 0): ?>
<div class="dropdown">
<a href="#"><?php echo tr('Sort by'); ?></a>
<ul>
<?php foreach ($options['sortBy'] as $field): ?>
<li><a href="#"><?php echo $model->getLabel($field); ?></a></li>
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
<label><input type="checkbox" />
</th>
<?php foreach ($options['columns'] as $column): ?>
<th<?php if ($column == $options['primary']) echo ' class="primary"'; ?>
 scope="col">
<?php echo $model->getLabel($column); ?>
</th>
<?php endforeach; ?>
<th class="actions" scope="col"><?php echo tr('Actions'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($records as $record): ?>
<tr>
<td class="selection">
<label>
<input type="checkbox" />
</label>
</td>
<?php foreach ($options['columns'] as $column): ?>
<?php if ($column == $options['primary']): ?>
<td class="primary">
<?php echo $Html->link(h($record->$column), $record->action($options['defaultAction'])); ?>
<div class="essential">
<?php foreach ($options['columns'] as $column): ?>
<span><?php echo $model->getLabel($column); ?>: <?php echo h($record->$column); ?></span>
<?php endforeach; ?>
</div>
<div class="action-links">
<?php foreach ($options['record']['actions'] as $action): ?>
<?php echo $Html->link($action['label'], $record->action($action['action'])); ?>
<?php endforeach; ?>
</div>
</td>
<?php else: ?>
<td>
<?php echo h($record->$column); ?>
</td>
<?php endif; ?>
<?php endforeach; ?>
<td class="actions">
<?php foreach ($options['record']['actions'] as $action): ?>
<?php echo $Html->link(
  $Icon->icon($action['icon']), $record->action($action['action']),
  array('title' => $action['label'])
); ?>
<?php endforeach; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php echo $this->embed('widgets/record-pagination.html', array('Pagination' => $Pagination)); ?>
