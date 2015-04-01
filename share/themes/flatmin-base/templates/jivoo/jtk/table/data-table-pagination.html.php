<div class="pagination">
<a href="#" class="table-settings icon icon-cog"></a>
<a href="#" class="table-find icon icon-binoculars"></a>
<strong>
<?php echo $Pagination->getFrom() . '&ndash;' . $Pagination->getTo(); ?>    
</strong>
of
<strong class="item-count"><?php echo $Pagination->getCount(); ?></strong>
<?php if ($Pagination->isFirst()): ?>
<button class="prev" disabled="disabled">
<span class="icon"><?php echo $Icon->icon('arrow-left2'); ?></span>
<span class="label">Previous</span>
</button>
<?php else: ?>
<a href="<?php echo $this->link($Pagination->prevLink()); ?>" class="prev button">
<span class="icon"><?php echo $Icon->icon('arrow-left2'); ?></span>
<span class="label">Previous</span>
</a>
<?php endif; ?>
<?php if ($Pagination->isLast()): ?>
<button class="next" disabled="disabled">
<span class="icon"><?php echo $Icon->icon('arrow-right2'); ?></span>
<span class="label">Next</span>
</button>
<?php else: ?>
<a href="<?php echo $this->link($Pagination->nextLink()); ?>" class="next button">
<span class="icon"><?php echo $Icon->icon('arrow-right2'); ?></span>
<span class="label">Next</span>
</a>
<?php endif; ?>
</div>
