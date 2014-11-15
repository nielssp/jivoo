<ul class="menu">
<?php foreach ($years as $year): ?>
<li><?php echo $Html->link($year['year'] . ' (' . $year['num'] . ')', array(
  'controller' => 'Posts',
  'action' => 'archive',
  $year['year']
)); ?>
<?php if (isset($year['months'])): ?>
<ul>
<?php foreach ($year['months'] as $month): ?>
<li><?php echo $Html->link($month['monthName'] . ' (' . $month['num'] . ')', array(
  'controller' => 'Posts',
  'action' => 'archive',
  $year['year'], $month['month']
)); ?></li>
<?php endforeach;?>
</ul>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
