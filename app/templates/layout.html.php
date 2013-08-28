<?php $this->style('style.css'); ?>
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
   
<?php echo $this->block('meta'); ?>
<?php echo $this->block('style'); ?>
<?php echo $this->block('script'); ?>

  </head>
  <body>
<?php echo $this->block('body-top'); ?>

    <h1><?php echo $Html->link($site['title']); ?></h1>
    <h2><?php echo $site['subtitle']; ?></h2>
<?php foreach ($Menu->getMenu('main') as $link) : ?>

    <?php echo $Html->link(h($link->title), $link); ?>

<?php endforeach; ?>

<?php echo $this->block('content'); ?>

<?php echo $this->block('body-bottom'); ?>
  </body>
</html>
