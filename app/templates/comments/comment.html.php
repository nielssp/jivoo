<?php
$classes = '';
if ($first) {
  $classes .= ' first';
}
switch ($comment->status) {
  case 'approved': break;
  case 'spam': $classes .= ' red'; break;
  default: $classes .= ' yellow';
}
?>
        <div class="record<?php echo $classes; ?>" id="record-<?php echo $comment->id; ?>">
          <div class="checkbox">
            <input type="checkbox" id="record-<?php echo $comment->id; ?>-checkbox"
              name="comments[<?php echo $comment->id; ?>]" value="selected" />
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
              array('stripAll' => true, 'maxLength' => 500, 'append' => '[...]')
            ); ?>
            </div>
          </div>
            <div class="clearl"></div>
          </div>