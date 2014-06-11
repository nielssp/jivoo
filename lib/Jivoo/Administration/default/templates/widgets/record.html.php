<tr<?php if (isset($class)) echo ' class="' . $class . '"'; ?>>
<td class="selection">
<label>
<input type="checkbox" />
</label>
</td>
<?php foreach ($columns as $column): ?>
<?php if ($column->primary): ?>
<td class="primary">
<?php echo $Html->link(h($column->getValue($record)), $record->action($options['defaultAction'])); ?>
<div class="essential">
<?php foreach ($columns as $column): ?>
<span><?php echo $model->getLabel($column); ?>:
<?php echo $column->getValue($record); ?></span>
<?php endforeach; ?>
</div>
<div class="action-links">
<?php foreach ($actions as $action): ?>
<?php echo $Html->link($action['label'], $record->action($action['action'])); ?>
<?php endforeach; ?>
</div>
</td>
<?php else: ?>
<td>
<?php echo $column->getValue($record); ?>
</td>
<?php endif; ?>
<?php endforeach; ?>
<td class="actions">
<?php foreach ($actions as $action): ?>
<?php echo $Html->link(
  $Icon->icon($action['icon']), $record->action($action['action']),
  array('title' => $action['label'])
); ?>
<?php endforeach; ?>
</td>
</tr>