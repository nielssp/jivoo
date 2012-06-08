<?php
$this->setHtmlIndent(4);
$this->insertStyle('theme-style', $this->getFile('css/style.css'));
?>
<!DOCTYPE html>
<html>
  <head>
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

<?php $this->outputHtml('head-meta'); ?>

<?php $this->outputHtml('head-styles'); ?>

<?php $this->outputHtml('head-scripts'); ?>
  </head>
  <body>
<?php $this->outputHtml('body-top'); ?>

    <h1><?php echo $site['title']; ?></h1>
    <h2><?php echo $site['subtitle']; ?></h2>

<?php foreach (Link::getMenu('main') as $link): ?>

    <?php $this->linkTo($link, $link->title); ?>

<?php endforeach; ?>
