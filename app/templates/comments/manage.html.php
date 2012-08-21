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
          <form action="<?php echo h($this->link(array())); ?>" method="get">
          <span class="filter block30 margin5">
            <input type="search" class="text" name="filter" value="<?php echo h($Filtering->query); ?>" />
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

      <form action="" method="post">
        <input type="hidden" name="access_token" value="<?php echo $accessToken; ?>" />
        <input type="hidden" name="filter" value="<?php echo $Filtering->query; ?>" />
        <input type="hidden" name="from" value="<?php echo $Pagination->getFrom(); ?>" />
        <input type="hidden" name="to" value="<?php echo $Pagination->getTo(); ?>" />
        <input type="hidden" name="count" value="<?php echo $Pagination->getCount(); ?>" />

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
              <li class="first"><a href="#">Approve</a></li>
              <li><input type="submit" name="approve" value="Approve" /></li>
              <li><a href="#">Unapprove</a></li>
              <li><a href="#">Spam</a></li>
              <li><input type="submit" name="notspam" value="Not spam" /></li>
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
            <input type="checkbox" name="comments[<?php echo $comment->id; ?>]" value="selected" />
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
            <?php echo $Html->link(h($comment->getPost()->title), $comment->getPost()); ?>
          </span>
          <span class="date block15 margin5">
            <?php echo $comment->formatDate(); ?>
          </span>
          <div class="actions">
           <ul class="menubutton">
<li class="first"><?php
switch($comment->status) {
  case 'approved':
    echo $Html->link('Unapprove',
      array('action' => 'edit', 'parameters' => array($comment->id)),
      array('class' => 'unapprove-action')
    );
    break;
  case 'spam':
    echo $Html->link('Not spam',
      array('action' => 'edit', 'parameters' => array($comment->id)),
      array('class' => 'approve-action')
    );
    break;
  default:
    echo $Html->link('Approve',
      array('action' => 'edit', 'parameters' => array($comment->id)),
      array('class' => 'approve-action')
    );
}
?>
</li>
<?php if ($comment->status != 'spam'): ?>
<li><?php
echo $Html->link('Spam',
  array('action' => 'edit', 'parameters' => array($comment->id)),
  array('class' => 'spam-action')
); 
?></li>
<?php endif; ?>
             <li><?php
             echo $Html->link('Edit',
               array('action' => 'edit', 'parameters' => array($comment->id))
             ); 
             ?></li>
             <li class="last red"><?php
             echo $Html->link('Delete',
               array('action' => 'delete', 'parameters' => array($comment->id)),
               array('class' => 'delete-action')
             ); 
             ?></li>
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

    </form>

<?php
$this->render('backend/footer.html');
?>

