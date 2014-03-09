<?php $this->extend('layout.html'); ?>
<div class="post">

<h1><?php echo h($post->title); ?></h1>
<?php if ($post->status == 'draft') : ?>
<p><strong>This post is a draft and is not visible to the pulic.</strong></p>
<?php endif; ?>

<?php echo $post->content; ?>

<?php
$tags = array();
foreach ($post->tags as $tag) {
  $tags[] = $Html->link(h($tag->tag), $tag);
}
?>
<div class="byline">
<?php
if (count($tags) > 0) {
  echo tr('Posted on %2 and tagged with %1{, }{ and }',
    $tags, fdate($post->createdAt));
}
else {
  echo tr('Posted on %1', fdate($post->createdAt));
}
?>
 | <a href="<?php echo $this->link($post); ?>#comment"><?php echo tr(
  'Leave a comment'); ?></a></div>

</div>
<?php
if ($Pagination->getCount() > 0) :
?>
<h1 id="comments"><?php echo tn('%1 comments', '%1 comment',
    $Pagination->getCount()); ?></h1>

<ul class="comments">
<?php
  $level = 0;
  foreach ($comments as $comment) :
    if (false && isset($comment->level)) {
      if ($level == $comment->level) {
        echo '</li>';
      }
      else if ($level > $comment->level) {
        for ($i = $comment->level; $level > $i; $i++)
          echo '</li></ul>';
        echo '</li>';
      }
      if ($level >= 0 AND $level < $comment->level)
        echo '<ul>';
    }
?>
  
<li>
<div class="comment-avatar">
<img src="http://1.gravatar.com/avatar/<?php
    if (!empty($comment->email))
      echo md5($comment->email);
    else
      echo md5($comment->ip);
                                       ?>?s=40&amp;d=monsterid&amp;r=G"
     alt="<?php echo h($comment->author); ?>"/>
</div>
<div class="comment" id="comment<?php echo $comment->id; ?>">
<h2><?php
    if (empty($comment->author)) {
      echo tr('Anonymous');
    }
    else {
      $website = $Html->cleanUrl($comment->website);
      if (empty($website))
        echo h($comment->author);
      else
        echo '<a href="' . $website . '">' . h($comment->author) . '</a>';
    }
    ?></h2>
  <p><?php echo $comment->content; ?></p>
<div class="byline">
<?php
    echo sdate($comment->createdAt);
    echo ' | <a href="#">' . tr('Reply') . '</a>';
?>
</div>
</div>
<div class="clear"></div>
<?php
    if (false && isset($comment->level))
      $level = $comment->level;
    else
      echo '</li>';
  endforeach;
  for ($i = $level; $i >= 0; $i--)
    echo '</li></ul>';
?>

<div class="pagination">
  <?php if (!$Pagination->isFirst())
    echo $Html->link('&#8592; Back ', $Pagination->prevLink('comments')); ?>
  <div class="right">
    <?php if (!$Pagination->isLast())
    echo $Html->link('More comments &#8594;', $Pagination->nextLink('comments')); ?>
  </div>
</div>
<?php
endif;
?>

<?php if ($post->commenting) : ?>

<h1 id="comment"><?php echo tr('Leave a comment'); ?></h1>
<?php if (!isset($newComment)) : ?></li>

<p><?php echo tr('Please log in to leave a comment.'); ?></p>
<?php 
  else : ?>

  <?php echo $Form->formFor($newComment, array('fragment' => 'comment')); ?>

<p><?php echo tr('Have something to say? Say it!'); ?></p>
<?php if ($user) : ?>

<p class="input">
<label>
  <?php echo tr('Logged in as %1.', h($user->username)) ?>
</label>
(<?php echo $Html->link(tr('Log out?'), 'Backend::logout') ?>)
</p>
<?php 
    else : ?>

<p class="input">
<?php echo $Form->label('author'); ?>
<?php echo $Form->ifRequired('author', '<span class="star">*</span>'); ?>
<?php echo $Form->text('author'); ?>
<?php echo $Form->error('author'); ?>
</p>

<p class="input">
<?php echo $Form->label('email'); ?>
<?php echo $Form->ifRequired('email', '<span class="star">*</span>'); ?>
<?php echo $Form->text('email'); ?>
<?php echo $Form->error('email'); ?>
</p>

<p class="input">
<?php echo $Form->label('website'); ?>
<?php echo $Form->ifRequired('website', '<span class="star">*</span>'); ?>
<?php echo $Form->text('website'); ?>
<?php echo $Form->error('website'); ?>
</p>
<?php endif; ?>

<p class="input">
<?php echo $Form->label('content'); ?>
<?php echo $Form->ifRequired('content', '<span class="star">*</span>'); ?>
<?php echo $Form->textarea('content'); ?>
<?php echo $Form->error('content'); ?>
</p>

<p><?php echo $Form->submit(tr('Post comment')); ?></p>
<?php echo $Form->end(); ?>

<?php endif; ?>

<?php endif; ?>

