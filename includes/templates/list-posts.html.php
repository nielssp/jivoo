<?php
/* 
 * Template for blog post listing
 */

include(PATH . INC . 'models/post.class.php');

// Render the header
$this->renderTemplate('header');

?>

<p>Blog listing</p>

<?php while ($post = $PEANUT['posts']->listPosts()): ?>
  
<h2><a href="<?php echo $post['link']; ?>"><?php echo $post['title']; ?></a></h2>

<p>Published <?php echo $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $post['date']); ?> -
<?php echo $PEANUT['i18n']->date($PEANUT['i18n']->timeFormat(), $post['date']); ?>
</p>

<?php echo $post['content']; ?>
  
<?php endwhile; ?>



<?php
// Render the footer
$this->renderTemplate('footer');
?>