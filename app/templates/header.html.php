<?php
$this->setIndent(4);
$this->insertStyle('theme-style', $this->file('css/style.css'));
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
<?php $this->output('head-meta'); ?>

<?php $this->output('head-styles'); ?>

<?php $this->output('head-scripts'); ?>
  </head>
  <body>
<?php $this->output('body-top'); ?>

    <h1><?php echo $Html->link($site['title']); ?></h1>
    <h2><?php echo $site['subtitle']; ?></h2>
<?php foreach ($Menu->getMenu('main') as $link) : ?>

    <?php echo $Html->link(h($link->title), $link); ?>

<?php endforeach; ?>
