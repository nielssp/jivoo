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
<h1><a href="<?php echo $this->link('path:'); ?>">
<?php echo $app['name']; ?>
</a></h1>
</header>

<div id="main">

<?php if (isset($title)): ?>
<h1><?php echo $title; ?>
<?php if (isset($subtitle)):?>
 <small><?php echo $subtitle; ?></small>
<?php endif; ?>
</h1>
<?php endif; ?>

<?php foreach ($this->helper('Notify') as $message): ?>
<div class="flash flash-<?php echo $message->type; ?>">
<?php echo $message; ?>
</div>
<?php endforeach; ?>

<?php echo $this->block('content'); ?>

<div id="install-progress" class="progress primary" style="display:none;">
<div class="progress-bar" style="width:0%">0%</div>
<div class="label"></div>
</div>

<pre id="install-status" style="display:none;">
</pre>

<?php
if (!isset($form)) {
  $Form->form(null);
  $form = $Form->end(); 
}
?>

<?php $Html->begin('div', 'class=install-buttons'); ?>

<?php
if (isset($enableNext)) {
  if ($enableNext)
    echo $Form->submit(tr('Next'), 'class=primary name=next');
  else
    echo $Form->submit(tr('Next'), 'class=primary disabled');
}
?>
<?php
if (isset($enableBack)) {
  if ($enableBack)
    echo $Form->submit(tr('Back'), 'name=back');
  else
    echo $Form->submit(tr('Back'), 'disabled');
}
?>

<?php $form->append($Html->end()); ?>

<?php echo $form; ?>

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
