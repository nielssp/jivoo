<?php
/*
 * Template for blog post
 */

// Render the header
$this->renderTemplate('header.html');
?>

<h2>
<?php echo $page->title; ?>
</h2>


<?php echo $page->content; ?>

<p>
<?php echo $Html->link('Permalink', $page); ?>
</p>

<?php
// Render the footer
$this->renderTemplate('footer.html');
?>