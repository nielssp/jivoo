<?php
$this->script('jquery.js');
$this->script('theme.js');
$this->style('theme.css');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php
if (isset($title)) {
  echo $title . ' | ' . $site['title'];
}
else {
  echo $site['title'];
  if (!empty($site['subtitle']))
    echo ' | ' . $site['subtitle'];
}
?></title>

<meta name="viewport" content="width=device-width, initial-scale=1" />

<?php echo $this->resourceBlock(); ?>
<!--[if (lt IE 9)]>
<?php echo $this->insertFile('respond.js'); ?>
<?php echo $this->insertFile('html5shiv.js'); ?>
<![endif]-->
</head>
<body>
<?php echo $this->block('body-top'); ?>

<div id="main">

<header>
<div class="title">
<h1><?php echo $Html->link($site['title']); ?></h1>
<h2><?php echo $site['subtitle']; ?></h2>
</div>
<?php
$rand = floor(time() / 60) % 5 + 1;
?>
<img src="<?php echo $this->file('img/banner.jpg'); ?>"
alt="<?php echo $site['title']; ?>" />
<nav>
<ul>
<?php foreach ($Menu->getMenu('main') as $link) : ?>
<li><?php echo $Html->link(h($link->title), $link); ?></li>
<?php endforeach; ?>
</ul>
</nav>
</header>

<div id="primary">
<div id="content">

<?php echo $this->block('content'); ?>

</div>
</div>

<aside>
<?php foreach ($Widgets->get('sidebar') as $widget): ?>
<div class="widget">
<div class="widget-title"><?php echo $widget['title']; ?></div>
<div class="widget-content"><?php echo $widget['content']; ?></div>
</div>
<?php endforeach; ?>

<?php echo $this->block('sidebar'); ?>
</aside>

<footer>
<h1><?php echo $Html->link($site['title']); ?></h1>
<div class="powered-by">
<?php echo $Html->link('Powered by Jivoo.', 'http://apakoh.dk'); ?>
</div>
</footer>
</div>


<?php echo $this->block('body-bottom'); ?>
  </body>
</html>
    
