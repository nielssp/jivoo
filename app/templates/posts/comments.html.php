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
            <?php if (FALSE): ?>
            <?php echo $Pagination->getFrom() . '-' . $Pagination->getTo(); ?>
            of <?php echo $Pagination->getCount(); ?>
            <?php else: ?>
            Page <?php echo $Pagination->getPage(); ?>
            of <?php echo $Pagination->getPages(); ?>
            <?php endif; ?>
          </span>
          <form action="<?php echo $this->link(array()); ?>" method="get">
          <span class="filter block30 margin5">
            <input type="search" class="text" name="filter" value="<?php echo h($Filtering->query); ?>" />
          </span>
          <div class="predefined block20">
            <ul class="menubutton">
              <li class="first"><a href="?filter=">All</a></li>
              <li><a href="?filter=status:approved">Approved</a></li>
              <li><a href="?filter=status:pending">Pending</a></li>
              <li class="last"><a href="?filter=status:spam">Spam</a></li>
          </div>
          </form>
          <span class="newer">&nbsp;
<?php if (!$Pagination->isFirst()) echo $Html->link('Newer &#8594;', $Pagination->prevLink()); ?>
          </span>
          <div class="clearl"></div>
        </div>
      </div>

      <div class="section bulk-actions">
        <div class="container">
          <div class="checkbox">
            <input type="checkbox" id="select-all-top" />
          </div>
          <div class="checkbox-text">
            <label for="select-all-top">Select all</label>
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

      <div class="section light_section">
        <div class="container">
<?php if (count($comments) < 1): ?>
          <div class="center">
          <?php echo tr('No comments matched your search criteria.')?>
          </div>
<?php endif; ?>
<?php $first = TRUE; ?>
<?php foreach ($comments as $comment): ?>

<?php
$classes = '';
if ($first) {
  $classes .= ' first';
  $first = FALSE;
}
switch ($comment->status) {
  case 'approved': break;
  case 'spam': $classes .= ' red'; break;
  default: $classes .= ' yellow';
}
?>

<div class="record<?php echo $classes; ?>">
          <div class="checkbox">
            <input type="checkbox" name="select-records" value="<?php echo $comment->id; ?>" />
         </div>
  <div class="header">
          <span class="author block20 margin5">
          
<?php
if (empty($comment->author)) {
  echo '<em>' . tr('Anonymous') . '</em>';
}
else {
  echo '<strong>' . h($comment->author) . '</strong>';
}
?>

          </span>
          <span class="title block30 margin5">
            <?php echo $Html->link(h($comment->getPost()->title), $comment); ?>
          </span>
          <span class="date block15 margin5">
            <?php echo $comment->formatDate(); ?>
          </span>
          <div class="actions">
           <ul class="menubutton">
<li class="first"><?php
switch($comment->status) {
  case 'approved':
    echo $Html->link('Unapprove', array());
    break;
  case 'spam':
    echo $Html->link('Not spam', array());
    break;
  default:
    echo $Html->link('Approve',
      array('action' => 'approveComment', 'parameters' => array($comment->id)),
      array('class' => 'approve-action')
    );
}
?>
</li>
<?php if ($comment->status != 'spam'): ?>
<li><a href="#">Spam</a></li>
<?php endif; ?>
             <li><a href="#">Edit</a></li>
             <li class="last red"><a href="#" class="delete-action">Delete</a></li>
           </ul>
         </div>
       </div>
            <div class="clearl"></div>
            <div class="body">
            <div class="author block20 margin5">
<?php
$website = $Html->cleanUrl($comment->website);
if (empty($comment->website))
  echo '<em>' . tr('No website') . '</em>';
else
  echo '<a href="' . $website . '">' . h($comment->website) . '</a>';
?><br/>
<?php 
if (empty($comment->email))
  echo '<em>' . tr('No email') . '</em>';
else 
  echo h($comment->email);
?><br/>
<?php echo h($comment->ip); ?>
<div class="comment-status">
<?php
switch ($comment->status) {
  case 'approved': echo tr('Approved'); break;
  case 'spam': echo tr('Marked as spam'); break;
  default: echo tr('Pending approval');
}
?>
</div>
            </div>
            <div class="content block75">
            <?php echo $comment->encode(
              'content',
              array('stripAll' => TRUE, 'maxLength' => 500, 'append' => '[...]')
            ); ?>
            </div>
          </div>
            <div class="clearl"></div>
          </div>
<?php endforeach; ?>
        </div>
      </div>

      <div class="section bulk-actions">
        <div class="container">
          <div class="checkbox">
            <input type="checkbox" id="select-all-bottom" />
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

<?php
$this->render('backend/footer.html');
?>

