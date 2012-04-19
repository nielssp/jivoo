<!DOCTYPE html>
<html>
  <head>
    <?php $this->outputHtml('head-top'); ?>

    <title>
<?php
if (isset($title)) {
  echo $title . ' | ' . $site['title'];
}
else {
  echo $site['title'] . ' | ' . $site['subtitle'];
}
?>
	</title>

    <link rel="stylesheet" type="text/css" href="<?php echo $this->getFile('css/style.css'); ?>" />

    <?php $this->outputHtml('head-bottom'); ?>
  </head>
  <body>
    <?php $this->outputHtml('body-top'); ?>

	<h1><?php echo $site['title']; ?></h1>
	<h2><?php echo $site['subtitle']; ?></h2>

<?php foreach (Link::getMenu('main') as $link): ?>

<?php $this->linkTo($link, $link->title); ?>

<?php endforeach; ?>
