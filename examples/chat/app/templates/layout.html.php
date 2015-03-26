<?php
$this->import('jquery.js');
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

<?php echo $this->block('content'); ?>

</body>
</html>
