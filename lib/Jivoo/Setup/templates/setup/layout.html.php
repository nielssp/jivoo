<?php
$this->meta('viewport', 'width=device-width, initial-scale=1');
$this->icon('jivoo.ico');
$this->import('core.css');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $title; ?></title>

<?php echo $this->block('meta'); ?>

<?php echo $this->resourceBlock(); ?>

</head>
<body>

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

<?php echo $Form->begin(); ?>

<?php echo $Form->hidden('install'); ?>

<?php echo $this->block('content'); ?>

<div class="install-buttons">
<?php
if ($enableNext)
  echo $Form->submit(tr('Next'), array('name' => 'next', 'class' => 'primary'));
else
  echo $Form->submit(tr('Next'), array('disabled' => 'disabled', 'class' => 'primary'));
?>
<?php
if ($enableBack)
  echo $Form->submit(tr('Back'), array('name' => 'back'));
else
  echo $Form->submit(tr('Back'), array('disabled' => 'disabled'));
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
