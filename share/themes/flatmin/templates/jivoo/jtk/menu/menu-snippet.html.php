<ul>
<?php foreach ($object as $item): ?>
<?php if ($item->isSeparator()): ?>
<li class="separator"><hr /></li>
<?php else: ?>
<?php 
if ($item->isMenu())
  $submenu = $Jtk->Menu($item);
else
  $submenu = '';

$current = ($this->isCurrent($item->route) or $item->childIsCurrent);
if (isset($item->parent) and $current)
  $object->childIsCurrent = true;
?>
<li>
<?php
$url = $this->link($item->route);
echo '<a';
if ($url != '') echo ' href="' . h($url) . '"';
if ($current) echo ' class="current"';
echo '>';
?>
<?php if (isset($item->icon)): ?>
<span class="icon"><?php echo $Icon->icon($item->icon); ?></span><?php endif; ?>
<span class="label"><?php echo h($item->label); ?></span>
<?php if (isset($item->badge)): ?>
<span class="badge"><?php echo h($item->badge); ?></span>
<?php endif; ?>
</a>
<?php echo $submenu; ?>
</li>
<?php endif; ?>
<?php endforeach; ?>
</ul>