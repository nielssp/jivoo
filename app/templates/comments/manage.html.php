<?php
// Render the header
$this->render('backend/header.html');
?>


      <div class="section light_section">
        <div class="container pagination">
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
            <input type="hidden" name="from" value="<?php echo $Pagination->getFrom(); ?>" />
            <input type="hidden" name="to" value="<?php echo $Pagination->getTo(); ?>" />
            <input type="hidden" name="count" value="<?php echo $Pagination->getCount(); ?>" />
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

<?php echo $Bulk->begin(); ?>

      <div class="section bulk-actions">
        <div class="container">
          <div class="checkbox">
            <input type="checkbox" value="all" name="all" id="select-all-top" />
          </div>
          <div class="checkbox-text">
            <label for="select-all-top">Select all
            (<?php echo $Html->link(
                tr('Select all %1 comments', $Pagination->getCount()),
                array('query' => array(
                  'filter' => $Filtering->query,
                  'from' => 1,
                  'to' => $Pagination->getCount()
                ))
              );
            ?>)
            </label>
          </div>
          <div class="actions">
            <ul class="menubutton">
            <?php $first = true; ?>
            <?php foreach ($Bulk->getActions() as $action): ?>
<?php
$classes = '';
if ($first) {
  $first = false;
  $classes .= ' first';
}
if ($action['type'] == 'delete') {
  $classes .= ' red';
}
?>
              <li class="<?php echo $classes; ?>">
                <input type="submit" name="<?php echo $action['name']; ?>"
                  value="<?php echo $action['label']; ?>" />
              </li>
            <?php endforeach; ?>
            </ul>
          </div>
          <div class="clearl"></div>
        </div>
      </div>

      <div class="section light_section">
        <div class="container">
<?php if (count($comments) < 1): ?>
          <div class="center">
          <?php echo tr('No comments matched your search criteria.')?>
          </div>
<?php endif; ?>
<?php
$this->first = true;
foreach ($comments as $this->comment) {
  $this->render('comments/comment.html');
  if ($this->first) {
    $this->first = false;
  }
}
?>
        </div>
      </div>

      <div class="section bulk-actions">
        <div class="container">
          <div class="checkbox">
            <input name="all" value="all" type="checkbox" id="select-all-bottom" />
          </div>
          <div class="checkbox-text">
            <label for="select-all-bottom">Select all</label>
          </div>
          <div class="actions">
            <ul class="menubutton">
              <li class="first"><a href="#">Approve</a></li>
              <li><a href="#">Unapprove</a></li>
              <li><a href="#">Spam</a></li>
              <li class="last red"><a href="#" class="delete-action">Delete</a></li>
            </ul>
          </div>
          <div class="clearl"></div>
        </div>
      </div>

<?php echo $Bulk->end(); ?>

<?php
$this->render('backend/footer.html');
?>

