<div class="pagination comments-pagination">
<?php if ($Pagination->isFirst()): ?>
<span class="prev"><?php echo '&#8592; ' . tr('Older'); ?></span>
<?php else: ?>
<?php echo $Html->link('&#8592; ' . tr('Older'), $Pagination->prevLink('comments'), array(
  'class' => 'prev'
)); ?>
<?php endif; ?>
<?php $previous = 0; ?>
<?php foreach ($Pagination->getPageList() as $page): ?>
<?php if ($page - $previous > 1) echo ' &hellip; '; ?>
<?php if ($page == $Pagination->getPage()): ?>
<span class="this"><?php echo $page; ?></span>
<?php else: ?>
<?php echo $Html->link($page, $Pagination->link($page, 'comments')); ?>
<?php endif;?> 
<?php $previous = $page; ?>
<?php endforeach; ?>
<?php if ($Pagination->isLast()): ?>
<span class="next"><?php echo tr('Newer') . ' &#8594'; ?></span>
<?php else: ?>
<?php echo $Html->link(tr('Newer') . ' &#8594', $Pagination->nextLink('comments'), array(
  'class' => 'next'
)); ?>
<?php endif; ?>
</div>
