      <div class="section light_section">
        <div class="container pagination" id="pagination"
            data-from="<?php echo $Pagination->getFrom(); ?>"
            data-to="<?php echo $Pagination->getTo(); ?>"
            data-count="<?php echo $Pagination->getCount(); ?>">
          <span class="older block15">
<?php if (!$Pagination->isLast()) echo $Html->link('&#8592; Older', $Pagination->nextLink()); ?>
          &nbsp;</span>
          <span class="pages block15">
            <?php if (false): ?>
            <?php echo $Pagination->getFrom() . '-' . $Pagination->getTo(); ?>
            of <?php echo $Pagination->getCount(); ?>
            <?php else: ?>
            Page <?php echo $Pagination->getPage(); ?>
            of <?php echo $Pagination->getPages(); ?>
            <?php endif; ?>
          </span>
          <form action="<?php echo h($this->link(array())); ?>" method="get">
            <span class="filter block30 margin5">
              <input type="search" class="text" name="filter" value="<?php echo h($Filtering->getQuery()); ?>" />
            </span>
            <div class="predefined block20">
              <ul class="menubutton">
                <li class="first"><a href="?filter=">All</a></li>
                <li><a href="?filter=status:approved">Approved</a></li>
                <li><a href="?filter=status:pending">Pending</a></li>
                <li class="last"><a href="?filter=status:spam">Spam</a></li>
              </ul>
            </div>
          </form>
          <span class="newer">&nbsp;
<?php if (!$Pagination->isFirst()) echo $Html->link('Newer &#8594;', $Pagination->prevLink()); ?>
          </span>
          <div class="clearl"></div>
        </div>
      </div>