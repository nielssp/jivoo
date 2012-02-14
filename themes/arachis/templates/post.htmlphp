<?php
/* 
 * Template for blog post
 */

// Render the header
$this->renderTemplate('header');

$post = $PEANUT['posts']->post;
?>

<div class="post">

<h1><?php echo $post['title']; ?></h1>

<?php echo $post['content']; ?>

<?php
$tags = array();
foreach ($post['tags'] as $name => $tag) {
  $tags[] = '<a href="#' . $name . '">' . $tag . '</a>';
}
?>
<div class="byline">
<?php
if (count($tags) > 0) {
  echo trl('Posted on %1 and tagged with %l', 'Posted on %1 and tagged with %l',
          ', ', ' and ', $tags, $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $post['date']));
}
else {
  echo tr('Posted on %1', $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $post['date']));
}
?>
 | <a href="<?php echo $post['link']; ?>#comment"><?php echo tr('Leave a comment'); ?></a></div>

</div>

<?php
if ($post['comments'] > 0):
?>
<h1><?php echo trn('%1 comment', '%1 comments', $post['comments']); ?></h1>


<ul class="comments">
<?php
$level = -1;
while ($comment = $PEANUT['posts']->listComments()):
  if (isset($comment['level'])) {
    if ($level == $comment['level']) {
      echo '</li>';
    }
    else if ($level > $comment['level']) {
      for ($i = $comment['level']; $level > $i; $i++)
        echo '</li></ul>';
      echo '</li>';
    }
    if ($level >= 0 AND $level < $comment['level'])
      echo '<ul>';
  }
?>
  
<li>
<div class="comment-avatar">
<img src="http://1.gravatar.com/avatar/<?php
if (isset($comment['email']))
  echo md5($comment['email']);
else
  echo md5($comment['ip']);
?>?s=40&amp;d=monsterid&amp;r=G"
     alt="<?php echo $comment['author']; ?>"/>
</div>
<div class="comment">
<h2><?php
if (empty($comment['author'])) {
  echo tr('Anonymous');
}
else {
  if (empty($comment['website']))
    echo $comment['author'];
  else
    echo '<a href="' . $comment['website'] . '">' . $comment['author'] . '</a>';
}
?></h2>
<p><?php echo $comment['content']; ?></p>
<div class="byline">
<?php
echo tr('%1 at %2', $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $comment['date']),
        $PEANUT['i18n']->date($PEANUT['i18n']->timeFormat(), $comment['date']));
if ($comment['reply'] == true)
  echo ' | <a href="#">' . tr('Reply') . '</a>';
?>
</div>
</div>
<div class="clear"></div>

<?php
  if (isset($comment['level']))
    $level = $comment['level'];
  else
    echo '</li>';
endwhile;
for ($i = $level; $i >= 0; $i--)
  echo '</li></ul>';
?>

<?php
endif;
?>

<a name="comment"></a>
<?php
if (isset($PEANUT['http']->params['reply-to']))
  echo '<h1>' . tr('Reply to comment') . '</h1>';
else
  echo '<h1>' . tr('Leave a comment') . '</h1>';
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