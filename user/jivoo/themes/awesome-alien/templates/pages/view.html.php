<?php $this->extend('layout.html'); ?>

<h1><?php echo h($page->title); ?></h1>
<?php if (!$page->published) : ?>
<p><strong><?php echo tr('This page is a draft and is not visible to the public.'); ?></strong></p>
<?php endif; ?>

<?php echo $Format->html($page, 'content'); ?>
