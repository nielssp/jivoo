<?php
$this->style('admin/icomoon/style.css');
$this->style('admin/theme.css'); 
$this->script('admin/respond.min.js');
$this->script('admin/html5shiv.js');
$this->script('admin/jquery.min.js'); 
$this->script('admin/theme.js'); 
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $title . ' | ' . $app['name']; ?></title>

<?php echo $this->block('meta'); ?>
<?php echo $this->block('style'); ?>
<?php echo $this->block('script'); ?>

</head>
<body>

<header>
<a href="#" class="toggle-menu"></a>
<h1><?php echo $app['name']; ?></h1>
<ul class="shortcuts">
<li><?php echo $Admin->link($site['title'], null, 'home'); ?></li>
<li><?php echo $Admin->link('Dashboard', 'Admin::dashboard', 'meter'); ?></li>
</ul>

<ul class="account">
<li><?php echo $Admin->link(h($user->username), 'Admin', 'user'); ?></li>
<li><?php echo $Admin->link('Log out', 'Admin::logout', 'exit'); ?></li>
<li class="account-menu notifications">
  <?php echo $Admin->link('3', array('url' => '#'), 'key'); ?>
<ul>
<li><a href="#"><span class="icon icon-bell"></span><span class="label">Notifications</span><span class="count">3</span></a></li>
<li><?php echo $Admin->link(h($user->username), 'Admin', 'user'); ?></li>
<li><?php echo $Admin->link('Log out', 'Admin::logout', 'exit'); ?></li>
</ul>
</li>
</ul>


</header>

<nav>

<?php echo $Admin->mainMenu(); ?>

</nav>

<div id="main">
<?php echo $this->block('content'); ?>
</div>

</body>
</html>