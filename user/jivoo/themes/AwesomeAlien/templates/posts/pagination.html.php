<div class="pagination">
<?php if ($Pagination->isFirst()): ?>
<span class="prev"><?php echo '&#8592; ' . tr('Newer'); ?></span>
<?php else: ?>
<?php echo $Html->link('&#8592; ' . tr('Newer'), $Pagination->prevLink(), array(
  'class' => 'prev'
)); ?>
<?php endif; ?>
<?php $previous = 0; ?>
<?php foreach ($Pagination->getPageList() as $page): ?>
<?php if ($page - $previous > 1) echo ' &hellip; '; ?>
<?php if ($page == $Pagination->getPage()): ?>
<span class="this"><?php echo $page; ?></span>
<?php else: ?>
<?php echo $Html->link($page, $Pagination->link($page)); ?>
<?php endif;?> 
<?php $previous = $page; ?>
<?php endforeach; ?>
<?php if ($Pagination->isLast()): ?>
<span class="next"><?php echo tr('Older') . ' &#8594'; ?></span>
<?php else: ?>
<?php echo $Html->link(tr('Older') . ' &#8594', $Pagination->nextLink(), array(
  'class' => 'next'
)); ?>
<?php endif; ?>
</div>
