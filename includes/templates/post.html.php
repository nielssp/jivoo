<?php
/* 
 * Template for blog post
 */

// Render the header
$this->renderTemplate('header');
?>

<h2><?php echo $PEANUT['posts']->post['title']; ?></h2>

<p>Published <?php echo $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $PEANUT['posts']->post['date']); ?> - 
<?php echo $PEANUT['i18n']->date($PEANUT['i18n']->timeFormat(), $PEANUT['posts']->post['date']); ?> 
<a href="<?php echo $PEANUT['http']->getLink(null, null, 'comment'); ?>">(<?php echo tr('Leave a comment'); ?>)</a>
</p>

<?php echo $PEANUT['posts']->post['content']; ?>

<h3>Tags</h3>
<?php
foreach ($PEANUT['posts']->post['tags'] as $name => $tag) {
  echo '<a href="#' . $name . '">' . $tag . '</a> ';
}
?>

<h3>Comments</h3>

<?php
while ($comment = $PEANUT['posts']->listComments()):
  ?>
  
  <div style="border-left:1px solid #000; padding-left:10px; margin-left: <?php echo (20*$comment['level']); ?>px">
  <a name="comment-<?php echo $comment['id']; ?>"></a>
  <p>Published by <?php
  if (empty($comment['website']))
    echo $comment['author'];
  else
    echo '<a href="' . $comment['website'] . '">' . $comment['author'] . '</a>';
  ?> on <?php echo $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $comment['date']); ?> -
  <?php echo $PEANUT['i18n']->date($PEANUT['i18n']->timeFormat(), $comment['date']); ?>
  </p>
  
  <?php echo $comment['content']; ?>
  <br/>
  <?php
  if ($comment['reply'] == true)
    echo '<a href="' . $PEANUT['http']->getLink(null, array('reply-to' => $comment['id'])) . '#comment" onclick="">Rely</a>';
  ?>
  </div>
  
  <?php
endwhile;
?>
<a name="comment"></a>
<?php
if (isset($PEANUT['http']->params['reply-to']))
  echo '<h3>' . tr('Reply to comment') . '</h3>';
else
  echo '<h3>' . tr('Leave a comment') . '</h3>';
?>

<p><?php echo tr('Leave a comment. Mandatory fields are marked with a %1.', '<span class="star">*</span>'); ?></p>

<form action="<?php echo $PEANUT['http']->getLink(null, null, 'comment'); ?>" method="post">

<input type="hidden" name="action" value="comment" />

<?php
if (isset($PEANUT['http']->params['reply-to']))
  echo '<input type="hidden" name="parent" value="' . $PEANUT['http']->params['reply-to'] . '" />';
?>

<?php echo $PEANUT['functions']->call('formInput', 'text', 'name', tr('Name'), $PEANUT['posts']->commentingInputs['name'], tr('Yout name'), 'mandatory', $PEANUT['posts']->commentingErrors['name']); ?>

<?php echo $PEANUT['functions']->call('formInput', 'text', 'email', tr('Email'), $PEANUT['posts']->commentingInputs['email'], tr('Your email address'), 'mandatory', $PEANUT['posts']->commentingErrors['email']); ?>

<?php echo $PEANUT['functions']->call('formInput', 'text', 'website', tr('Website'), $PEANUT['posts']->commentingInputs['website'], tr('Your website'), 'optional', $PEANUT['posts']->commentingErrors['website']); ?>

<?php echo $PEANUT['functions']->call('formInput', 'textarea', 'comment', tr('Comment'), $PEANUT['posts']->commentingInputs['comment'], tr('Your comment'), 'mandatory', $PEANUT['posts']->commentingErrors['comment']); ?>

<?php echo $PEANUT['functions']->call('formInput', 'submit', '', 'Submit'); ?>

</form>

<?php
// Render the footer
$this->renderTemplate('footer');
?>