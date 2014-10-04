<?php $this->extend('layout.html'); ?>

<h1><?php echo $page->title; ?></h1>
<?php echo $Format->html($page, 'content'); ?>
