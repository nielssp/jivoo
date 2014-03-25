<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo $basicStyle; ?>" />

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
