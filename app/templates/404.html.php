<?php
/* 
 * Template for "404 not found"
 */

// Render the header
$this->render('header');
?>

<h2 ><?php echo tr('Page not found'); ?></h2>

<p><?php echo tr('The page you were looking for could not found.'); ?></p>

<?php
// Render the footer
$this->render('footer');
?>
