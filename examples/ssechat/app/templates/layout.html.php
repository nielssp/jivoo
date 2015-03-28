<?php
$this->import('jquery.js');
$this->import('chat.css');
$this->meta('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html>
<head>
<title>Jivoo chat app</title>

<?php echo $this->block('meta'); ?>

<?php echo $this->resourceBlock(); ?>

</head>
<body>

<div id="main">
<header>
<?php echo $Html->link('Jivoo chat app', null, array('class' => 'title')); ?>
</header>

<div id="primary">

<?php echo $this->block('content'); ?>

</div>
</div>

</body>
</html>
