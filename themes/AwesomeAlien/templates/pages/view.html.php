<?php $this->extend('layout.html'); ?>

<h1><?php echo h($page->title); ?></h1>
<?php if (!$page->published) : ?>
<p><strong>This page is a draft and is not visible to the pulic.</strong></p>
<?php endif; ?>

<?php echo $page->content; ?>
