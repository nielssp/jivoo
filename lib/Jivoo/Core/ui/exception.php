<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<?php include dirname(__FILE__) . '/basic.css'; ?>
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
  $file = $exception->getFile();
  $line = $exception->getLine();
  $message = $exception->getMessage();
?>
<h2><?php echo $message ?></h2>

<p><?php echo tr(
  'An uncaught %1 was thrown in file %2 on line %3 that prevented further execution of this request.',
  '<strong>' . get_class($exception) . '</strong>',
  '<em>' . basename($file) . '</em>', '<strong>' . $line . '</strong>'
); ?></p>
<h2><?php echo tr('Where it happened') ?></h2>
<p><code><?php echo $file ?></code></p>
<h2><?php echo tr('Stack Trace') ?></h2>
<table class="trace">
<thead>
<tr>
<th><?php echo tr('File') ?></th>
<th><?php echo tr('Line') ?></th>
<th><?php echo tr('Class') ?></th>
<th><?php echo tr('Function') ?></th>
<th><?php echo tr('Arguments') ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($exception->getTrace() as $i => $trace): ?>
<tr class="<?php echo (($i % 2 == 0) ? 'even' : 'odd') ?>">
<td>
<span title="<?php echo (isset($trace['file']) ? $trace['file'] : '') ?>">
<?php echo (isset($trace['file']) ? basename($trace['file']) : '') ?>
</span>
</td>
<td><?php echo (isset($trace['line']) ? $trace['line'] : '') ?></td>
<td><?php echo (isset($trace['class']) ? $trace['class'] : '') ?></td>
<td><?php echo (isset($trace['function']) ? $trace['function'] : '') ?></td>
<td>
<?php if (isset($trace['args'])): ?>
<?php foreach ($trace['args'] as $j => $arg): ?>
<span title="<?php
if (is_scalar($arg)) {
  echo h($arg);
}
else if (is_object($arg)) {
  echo get_class($arg);
}
else if (is_array($arg)) {
  echo count($arg);
}
?>"><?php echo gettype($arg) ?>
</span><?php echo ($j < count($trace['args']) - 1 ? ', ' : '') ?>
<?php endforeach; ?>
<?php else: ?>
null
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody>
</table>
<?php
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