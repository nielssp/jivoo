<?php
$this->meta('viewport', 'width=device-width, initial-scale=1');
$this->import('icomoon/style.css', 'jivoo/core.css', 'jivoo/jtk/theme.css');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo tr('Log in'); ?> | <?php echo $site['title']; ?></title>

<?php echo $this->block('meta'); ?>

<?php echo $this->resourceBlock(); ?>

</head>
<body id="login">

<div>

<?php echo $this->block('content'); ?>

</div>

</body>
</html>