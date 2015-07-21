<tr<?php if (isset($row->class)) echo ' class="' . $row->class . '"'; ?>>
<td class="selection">
<label>
<input type="checkbox" name="selection" value="<?php echo $row->id; ?>" />
</label>
</td>
<?php foreach ($row->cells as $cell): ?>
<?php if ($cell->column->primary): ?>
<td class="main">
<?php echo $cell->value; ?>
<dl class="values">
<?php foreach ($row->cells as $cell2): ?>
<?php if (!$cell2->column->primary): ?>
<dt><?php echo $cell2->column->label; ?></dt>
<dd><?php echo $cell2->value; ?></dd>
<?php endif; ?>
<?php endforeach; ?>
</dl>
<div class="action-links">
<?php foreach ($row->actions as $action): ?>
<?php echo $Icon->link(
  $action->label,
  $this->mergeRoutes($action->route, array($row->id)),
  $action->icon, null,
  array(
    'class' => 'button button-sm',
    'data' => array(
      'method' => $action->method,
      'data' => json_encode($action->data),
      'confirm' => $action->confirmation
    )
  )
); ?> 
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
<div class="button-group">
<?php foreach ($row->actions as $action): ?>
<?php echo $Icon->iconLink(
  $action->label,
  $this->mergeRoutes($action->route, array($row->id)),
  $action->icon,
  array(
    'class' => 'button button-sm',
    'data' => array(
      'method' => $action->method,
      'data' => json_encode($action->data),
      'confirm' => $action->confirmation
    )
  )
); ?>
<?php endforeach; ?>
</div>
</td>
</tr>