<?php
/* 
 * Template for when no other template is sufficient
 */

// Render the header
$this->renderTemplate('header');
?>

<?php
if (isset($parameters['title'])) {
  echo '<h2>' . $parameters['title'] . '</h2>';
}
?>

<?php echo $parameters['content'] ?>

<?php
// Render the footer
$this->renderTemplate('footer');
?>