<tr<?php if (isset($row->class)) echo ' class="' . $row->class . '"'; ?>>
<td class="selection">
<label>
<input type="checkbox" name="selection" value="<?php echo $row->id; ?>" />
</label>
</td>
<?php foreach ($row->cells as $cell): ?>
<?php if ($cell->column->primary): ?>
<td class="primary">
<?php echo $cell->value; ?>
<div class="essential">
<?php foreach ($row->cells as $cell2): ?>
<?php if (!$cell2->column->primary): ?>
<span><?php echo $cell2->column->label; ?>:
<?php echo $cell2->value; ?></span>
<?php endif; ?>
<?php endforeach; ?>
</div>
<div class="action-links">
<?php foreach ($row->actions as $action): ?>
<?php echo $Html->link(h($action->label), $this->mergeRoutes($action->route, array($row->id))); ?>
<?php endforeach; ?>
</div>
</td>
<?php else: ?>
<td class="non-essential">
<?php echo $cell->value; ?>
</td>
<?php endif; ?>
<?php endforeach; ?>
<td class="actions non-essential">
<?php foreach ($row->actions as $action): ?>
<?php echo $Html->link(
  $Icon->icon($action->icon),
  $this->mergeRoutes($action->route, array($row->id)),
  array(
    'title' => $action->label,
    'data' => array(
      'method' => $action->method,
      'data' => json_encode($action->data),
      'confirm' => $action->confirmation
    )
  )
); ?>
<?php endforeach; ?>
</td>
</tr>