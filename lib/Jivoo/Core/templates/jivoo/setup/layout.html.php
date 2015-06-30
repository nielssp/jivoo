<?php
$this->meta('viewport', 'width=device-width, initial-scale=1');
$this->icon('jivoo/jivoo-red.ico');
$this->import('jivoo/core.css');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $title; ?></title>

<?php echo $this->block('meta'); ?>

<?php echo $this->resourceBlock(); ?>

</head>
<body class="narrow">

<header>
<h1><?php echo $app['name']; ?></h1>
</header>

<div id="main">

<?php if (isset($title)): ?>
<h1><?php echo $title; ?></h1>
<?php endif; ?>

<?php foreach ($flash as $message): ?>
<div class="flash flash-<?php echo $message->type; ?>">
<?php echo $message; ?>
</div>
<?php endforeach; ?>

<?php echo $this->block('content'); ?>

<div id="install-status">
</div>

<?php
if (!$Form->isOpen())
  echo $Form->form(null);
?>

<div class="install-buttons">
<?php
if (isset($enableNext)) {
  if ($enableNext)
    echo $Form->submit(tr('Next'), array('name' => 'next', 'class' => 'primary'));
  else
    echo $Form->submit(tr('Next'), array('disabled' => 'disabled', 'class' => 'primary'));
}
?>
<?php
if (isset($enableBack)) {
  if ($enableBack)
    echo $Form->submit(tr('Back'), array('name' => 'back'));
  else
    echo $Form->submit(tr('Back'), array('disabled' => 'disabled'));
}
?>
</div>

<?php echo $Form->end(); ?>

</div>

<footer>
<?php if (isset($app['website'])): ?>
<?php echo $Html->link($app['name'] . ' ' . $app['version'], $app['website']); ?>
<?php else: ?>
<?php echo $app['name'] . ' ' . $app['version']; ?> 
<?php endif; ?>
</footer>

</body>
</html>
