<?php
/*
 * Template for blog post
 */

// Render the header
$this->render('header.html');
?>

<h2>
<?php echo $page->title; ?>
</h2>


<?php echo $page->content; ?>

<p>
<?php echo $Html->link('Permalink', $page); ?> |
<?php echo $Html->link('Edit', array('action' => 'edit', 'parameters' => array($page->id)); ?>
</p>

<?php
// Render the footer
$this->render('footer.html');
?>
