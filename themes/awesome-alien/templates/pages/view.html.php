<?php
/* 
 * Template for blog post
 */

// Render the header
$this->render('header');
?>

<h1><?php echo h($page->title); ?></h1>

<?php echo $page->content; ?>

<p>
  <?php echo $Html->link('Permalink', $page); ?>
</p>


<?php
// Render the footer
$this->render('footer');
?>
