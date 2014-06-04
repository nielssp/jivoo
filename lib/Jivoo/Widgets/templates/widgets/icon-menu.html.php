<ul>
<?php foreach ($menu as $item): ?>
<?php
$submenu = '';
if ($item instanceof IconMenu) {
  $submenu = $Widget->widget('IconMenu', array(
  	'menu' => $item
  ));
}
?>
<li>
<?php echo $Icon->link($item->label, $item->route, $item->icon, $item->badge); ?>

<?php echo $submenu; ?>
</li>
<?php endforeach; ?>
</ul>
