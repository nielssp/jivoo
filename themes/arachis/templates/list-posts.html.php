<?php
/* 
 * Template for blog post listing
 */

// Render the header
$this->renderTemplate('header.html');

?>

<?php
while ($post = $PEANUT['posts']->listPosts()):
?>

<div class="post">
<h1><a href="<?php echo $post['link']; ?>"><?php echo $post['title']; ?></a></h1>

<?php echo $post['content']; ?>

<?php if ($post['more']) echo '<p><a href="' . $post['link'] . '">' . tr('Continue reading...') . '</a></p>'; ?>

<div class="byline"><?php echo tr('Posted on %1', $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $post['date'])); ?>
 | <a href="<?php echo $post['link']; ?>#comment">
<?php
if ($post['comments'] == 0)
  echo tr('Leave a comment');
else
  echo trn('%1 comment', '%1 comments', $post['comments']);
?></a></div>
</div>
<?php
endwhile;
?>

<?php
// Render the footer
$this->renderTemplate('footer.html');
?>