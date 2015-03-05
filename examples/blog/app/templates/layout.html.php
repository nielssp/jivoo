<?php
$this->import('blog.css');
$this->meta('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html>
<head>
<title><?php
if (isset($title))
  echo h($title) . ' | ';
echo 'My Blog';
?></title>

<?php echo $this->block('meta'); ?>

<?php echo $this->resourceBlock(); ?>

</head>
<body>

<div id="main">
<header>
<?php echo $Html->link('Jivoo Blog Example', null, array('class' => 'title')); ?>

<nav>
<ul>
<li><?php echo $Html->link('Home', null); ?></li>
<?php if ($Auth->isLoggedIn()): ?>
<li><?php echo $Html->link('Add post', 'Posts::add'); ?></li>
<li><?php echo $Html->link('Log out', 'App::logout'); ?></li>
<?php else: ?>
<li><?php echo $Html->link('Admin', 'App::login'); ?></li>
<?php endif; ?>
</ul>
</nav>
</header>

<div id="content">
<?php echo $this->block('content'); ?>
</div>

</div>

</body>
</html>