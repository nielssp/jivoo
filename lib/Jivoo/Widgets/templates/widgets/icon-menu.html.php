<?php
$containsCurrent = false; 
?>
<ul>
<?php foreach ($menu as $item): ?>
<?php
$submenu = '';
$current = false;
if ($item instanceof IconMenu) {
  $this->submenuIsCurrent = false;
  $submenu = $Widget->widget('IconMenu', array(
  	'menu' => $item
  ));
  if ($this->submenuIsCurrent === true) {
    $current = true;
  }
}
if (!$current)
  $current = $this->isCurrent($item->route);
if ($current)
  $containsCurrent = true;
?>
<li>
<a href="<?php echo h($this->link($item->route)); ?>"<?php
if ($current) echo ' class="current"'; ?>>
<?php if (isset($item->icon)): ?>
<span class="icon">
<?php echo $Icon->icon($item->icon); ?>
</span>
<?php endif; ?>
<span class="label">
<?php echo $item->label; ?>
</span>
<?php if (isset($item->badge)): ?>
<span class="count"><?php echo $item->badge; ?></span>
<?php endif; ?>
</a>
<?php echo $submenu; ?>
</li>
<?php endforeach; ?>
</ul>

<?php 
if ($containsCurrent)
  $this->submenuIsCurrent = true;
?>