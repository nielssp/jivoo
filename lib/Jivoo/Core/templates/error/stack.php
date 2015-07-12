<table class="trace">
<thead>
<tr>
<th><?php echo tr('File') ?></th>
<th><?php echo tr('Class') ?></th>
<th><?php echo tr('Function') ?></th>
<th><?php echo tr('Arguments') ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($trace as $element): ?>
<tr>
<td>
<span title="<?php echo (isset($element['file']) ? $element['file'] : '') ?>">
<?php echo (isset($element['file']) ? basename($element['file']) : '') ?>
<?php echo (isset($element['line']) ? ' : ' . $element['line'] : '') ?>
</span></td>
<td><?php echo (isset($element['class']) ? $element['class'] : '') ?></td>
<td><?php echo (isset($element['function']) ? $element['function'] : '') ?></td>
<td>
<?php if (isset($element['args'])): ?>
<?php foreach ($element['args'] as $j => $arg): ?>
<span title="<?php
if (is_scalar($arg)) {
  echo h($arg);
}
else if (is_object($arg)) {
  echo get_class($arg);
}
else if (is_array($arg)) {
  echo count($arg);
}
?>"><?php echo gettype($arg) ?>
</span><?php echo ($j < count($element['args']) - 1 ? ', ' : '') ?>
<?php endforeach; ?>
<?php else: ?>
null
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody>
</table>