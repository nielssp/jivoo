<!DOCTYPE html>
<html>
<head>
<title><?php echo $this->app['title']; ?></title>

</head>
<body>

<?php echo $this->block('content'); ?>

<p>Menu:</p>
<ul>
<li><?php echo $Html->link('Frontpage', null); ?></li>
<li><?php echo $Html->link('Example page 1', array('snippet:Page', 'example')); ?></li>
</ul>

</body>
</html>
