<?php
$this->style('core.css');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $title; ?></title>

<?php echo $this->block('meta'); ?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="shortcut icon" href="<?php echo $this->file('img/jivoo.ico'); ?>" />
<?php echo $this->block('style'); ?>
<?php echo $this->block('script'); ?>
</head>
<body>

<div id="header">
<div class="right"><?php echo $app['name']; ?></div>
</div>

<div id="content">

<?php echo $this->block('content'); ?>

</div>

<div class="footer" id="poweredby">
<?php if (isset($app['website'])): ?>
<?php echo $Html->link(
  $app['name'] . ' ' . $app['version'],
  $app['website']
); ?>
<?php else: ?>
<?php echo $app['name']; ?> 
<?php echo $app['version']; ?>
<?php endif; ?>
</div>

<div class="footer" id="links">
<a href="http://apakoh.dk">Jivoo</a>
</div>

</body>
</html>
