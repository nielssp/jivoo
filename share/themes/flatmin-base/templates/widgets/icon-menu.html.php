<ul>
<?php foreach ($menu as $item): ?>
<?php
$current = $this->isCurrent($item->route, $options['defaultAction'], $options['defaultParameters']);
?>
<li>
<a href="<?php echo h($this->link($item->route)); ?>"<?php
if ($current) echo ' class="current"'; ?>>
<?php if (isset($item->icon)): ?>
<span class="icon"><?php echo $Icon->icon($item->icon); ?></span><?php endif; ?>
<span class="label"><?php echo $item->label; ?></span>
<?php if (isset($item->badge)): ?>
<span class="count"><?php echo $item->badge; ?></span>
<?php endif; ?>
</a>
<?php if ($item instanceof IconMenu): ?>
<?php echo $this->embed('widgets/icon-menu.html', array(
  'menu' => $item,
  'options' => $options
)); ?>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>