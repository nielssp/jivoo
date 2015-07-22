<!DOCTYPE html>
<html>
<head>
<title><?php echo h($this->app['title']); ?></title>
</head>
<body>
<?php echo $this->block('content'); ?>
<p>Menu:</p>
<ul>
<li><a class="<?php if ($this->isCurrent(null)) echo 'current'; ?>" href="<?php echo $this->link(null); ?>">Frontpage</a></li>
<li><a class="<?php if ($this->isCurrent(array('action:Pages::view', 'example'))) echo 'current'; ?>" href="<?php echo $this->link(array('action:Pages::view', 'example')); ?>">Example page 1</a></li>
</ul>
</body>
</html>
