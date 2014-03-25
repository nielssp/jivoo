<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<?php include CORE_LIB_PATH . '/ui/basic.css'; ?>
</style>
</head>
<body class="exception">

<div id="header">
<div class="right"><?php echo $app; ?></div>
</div>

<div id="content">
<div class="section">
<div class="container">
<div id="sad">
:-(
</div>
<h1><?php echo $title; ?></h1>

<div class="clearl"></div>

<?php
if (isset($exception)) {
  include CORE_LIB_PATH . '/ui/exception.php';
}
else {
  echo $body;
}
?>

</div>
</div>
</div>

<div class="footer" id="poweredby">
<a href="#"><?php echo $app; ?> 
<?php echo $version; ?></a>
</div>

<div class="footer" id="links">
<a href="http://apakoh.dk">Jivoo</a>
</div>

</body>
</html>