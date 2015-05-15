<ul>
<?php foreach ($object as $item): ?>
<?php if ($item->isSeparator()): ?>
<li class="separator"><hr /></li>
<?php else: ?>
<?php
$current = $this->isCurrent($item->route);
?>
<li>
<?php
$url = $this->link($item->route);
if ($url != ''):
?>
<a href="<?php echo h($url); ?>"<?php
if ($current) echo ' class="current"'; ?>>
<?php else: ?>
<a>
<?php endif; ?>
<?php if (isset($item->icon)): ?>
<span class="icon"><?php echo $Icon->icon($item->icon); ?></span><?php endif; ?>
<span class="label"><?php echo $item->label; ?></span>
<?php if (isset($item->badge)): ?>
<span class="count"><?php echo $item->badge; ?></span>
<?php endif; ?>
</a>
<?php if ($item->isMenu()): ?>
<?php echo $Jtk->Menu($item); ?>
<?php endif; ?>
</li>
<?php endif; ?>
<?php endforeach; ?>
</ul>