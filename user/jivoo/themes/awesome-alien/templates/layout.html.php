<?php
$this->import('jquery.js', 'theme.js', 'theme.css', 'respond.js', 'html5shiv.js');
$this->meta('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php
if (isset($title)) {
  echo h($title . ' | ' . $site['title']);
}
else {
  echo h($site['title']);
  if (!empty($site['subtitle']))
    echo ' | ' . h($site['subtitle']);
}
?></title>


<?php echo $this->block('meta'); ?>

<?php echo $this->resourceBlock(); ?>

</head>
<body>
<?php echo $this->block('body-top'); ?>

<div id="main">

<header>
<div class="title">
<h1><?php echo $Html->link($site['title']); ?></h1>
<h2><?php echo $site['subtitle']; ?></h2>
</div>
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
<?php if (!empty($widget['title'])): ?>
<div class="widget-title"><?php echo $widget['title']; ?></div>
<?php endif; ?>
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
    
