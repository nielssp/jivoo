<?php
/* 
 * Template for blog post
 */

// Render the header
$this->renderTemplate('header');
?>

<h2><?php echo $PEANUT['pages']->page['title']; ?></h2>


<?php echo $PEANUT['pages']->page['content']; ?>

<p><a href="<?php echo $PEANUT['http']->getLink($PEANUT['templates']->getPath('page', array('p' => $PEANUT['pages']->page['id']))); ?>">Permalink</a></p>

<?php
// Render the footer
$this->renderTemplate('footer');
?>