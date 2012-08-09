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
            Page <?php echo $Pagination->getPage(); ?>
            of <?php echo $Pagination->getPages(); ?>
          </span>
          <form action="<?php echo $this->link(array()); ?>" method="get">
          <span class="filter block30">
            <input type="text" class="text" name="filter" value="<?php echo h($filter); ?>" />
          </span>
          </form>
          <span class="newer">&nbsp;
<?php if (!$Pagination->isFirst()) echo $Html->link('Newer &#8594;', $Pagination->prevLink()); ?>
          </span>
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

          <div class="record<?php if ($first) { echo ' first'; $first = FALSE; } ?>">
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
             <li class="first"><a href="#">Approve</a></li>
             <li><a href="#">Spam</a></li>
             <li><a href="#">Edit</a></li>
             <li class="last"><a href="#">Delete</a></li>
           </ul>
          </div>
            <div class="clearl"></div>
            <div class="author block20 margin5 topspace">
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
<div class="topspace">
<strong><?php echo ucfirst(h($comment->status))?></strong>
</div>
            </div>
            <div class="content block75 topspace">
            <?php echo $comment->encode(
              'content',
              array('stripAll' => TRUE, 'maxLength' => 500, 'append' => '[...]')
            ); ?>
            </div>
            <div class="clearl"></div>
          </div>
<?php endforeach; ?>
        </div>
      </div>

<?php
$this->render('backend/footer.html');
?>

