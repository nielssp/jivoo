<?php
/*
 * Template for blog post
 */

// Render the header
$this->renderTemplate('header');
?>

<h2><?php echo $post->title; ?></h2>

<p>Published <?php echo $post->formatDate(); ?>
  - <?php echo $post->formatTime(); ?>
  <a href="#comment">
    (<?php echo tr('Leave a comment'); ?>)
  </a>
</p>

<?php echo $post->content; ?>

<?php $tags = $post->getTags(); ?>

<?php if (count($tags) > 0): ?>
<h3>Tags</h3>
<?php
foreach ($tags as $tag) {
  $this->linkTo($tag, $tag->tag);
  echo ' ';
}
endif;
?>

<h3>Comments</h3>

<?php
foreach ($post->getComments() as $comment):
?>

<div style="border-left:1px solid #000; padding-left:10px; margin-left: 20px">

<p>
<?php $this->linkTo($comment, '#'); ?>
Published by <?php
if ($comment->website == '')
  echo $comment->author;
else
  echo '<a href="' . $comment->website . '">' . $comment->author . '</a>';
?> on <?php echo $comment->formatDate(); ?> -
<?php echo $comment->formatTime(); ?>
</p>

<?php echo $comment->content; ?>
</div>

<?php
endforeach;

goto a;
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
a:
// Render the footer
$this->renderTemplate('footer');
?>