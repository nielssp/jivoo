<tr<?php if (isset($class)) echo ' class="' . $class . '"'; ?>>
<td class="selection">
<label>
<input type="checkbox" name="selection" value="<?php echo $id; ?>" />
</label>
</td>
<?php $i = 0; ?>
<?php foreach ($columns as $column): ?>
<?php if ($column == $primaryColumn): ?>
<td class="primary">
<?php echo $cells[$i]; ?>
<div class="essential">
<?php $j = 0; ?>
<?php foreach ($columns as $column): ?>
<?php if ($column != $primaryColumn): ?>
<span><?php echo $labels[$column]; ?>:
<?php echo $cells[$j]; ?></span>
<?php endif; ?>
<?php $j++; ?>
<?php endforeach; ?>
</div>
<div class="action-links">
<?php foreach ($actions as $action): ?>
<?php echo $Html->link(h($action->label), $this->mergeRoutes($action->route, array($options['id']))); ?>
<?php endforeach; ?>
</div>
</td>
<?php else: ?>
<td class="non-essential">
<?php echo $cells[$i]; ?>
</td>
<?php endif; ?>
<?php $i++; ?>
<?php endforeach; ?>
<td class="actions non-essential">
<?php foreach ($actions as $action): ?>
<?php echo $Html->link(
  $Icon->icon($action->icon),
  $this->link($this->mergeRoutes($action->route, array($id))),
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