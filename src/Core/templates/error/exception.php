<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $title; ?></title>

<meta name="generator" content="Jivoo" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style type="text/css">
<?php include dirname(__FILE__) . '/../../assets/css/jivoo/core.css'; ?>
</style>
</head>
<body class="exception">

<header>
<h1><?php echo $app; ?></h1>
</header>

<div id="main">
<div id="sad">
:-(
</div>
<h1><?php echo $title; ?></h1>

<?php
if (isset($exception)) {
  $file = $exception->getFile();
  $line = $exception->getLine();
  $record = $exception->getMessage();
?>
<h2><?php echo $record; ?></h2>

<p><?php echo tr(
  'An uncaught %1 was thrown in file %2 on line %3 that prevented further execution of this request.',
  '<strong>' . get_class($exception) . '</strong>',
  '<em>' . basename($file) . '</em>', '<strong>' . $line . '</strong>'
); ?></p>
<p><?php echo tr('The exception was thrown from the following file:')?></p>
<p><code><?php echo $file ?></code></p>
<h2><?php echo tr('Stack trace') ?></h2>
<?php $trace = $exception->getTrace(); ?>
<?php include dirname(__FILE__) . '/stack.php'; ?>

<?php $previous = $exception->getPrevious(); ?>
<?php while (isset($previous)): ?>

<h2><?php echo tr('Caused by'); ?></h2>

<p><?php echo tr(
  '%1 in file %2 on line %3:',
  '<strong>' . get_class($previous) . '</strong>',
  '<em title="' . $previous->getFile() . '">' . basename($previous->getFile()) . '</em>',
  '<strong>' . $previous->getLine() . '</strong>'
); ?>

<?php echo $previous->getMessage(); ?></p>

<?php $trace = $previous->getTrace(); ?>
<?php include dirname(__FILE__) . '/stack.php'; ?>

<?php $previous = $previous->getPrevious(); ?>
<?php endwhile; ?>

<h2><?php echo tr('System'); ?></h2>

<dl>
<dt><?php echo tr('%1 version', $app); ?></dt>
<dd><?php echo $version; ?></dd>

<dt><?php echo tr('%1 version', 'Jivoo'); ?></dt>
<dd><?php echo \Jivoo\VERSION; ?></dd>

<dt><?php echo tr('PHP version'); ?></dt>
<dd><?php echo phpversion(); ?></dd>

<dt><?php echo tr('Server API'); ?></dt>
<dd><?php echo php_sapi_name(); ?></dd>

<dt><?php echo tr('Web server'); ?></dt>
<dd><?php echo $_SERVER['SERVER_SOFTWARE']; ?></dd>

<dt><?php echo tr('Operating system'); ?></dt>
<dd><?php echo php_uname(); ?></dd>
</dl>

<h2><?php echo tr('Log messages')?></h2>

<table class="trace">
<thead>
<tr>
<th style="width: 50%;"><?php echo tr('Message') ?></th>
<th><?php echo tr('File') ?></th>
<th><?php echo tr('Time') ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($log as $record): ?>
<?php $context = $record['context']; ?>
<?php $message = \Jivoo\Core\Log\Logger::interpolate($record['message'], $context); ?>
<tr>
<td>
[<?php echo $record['level']; ?>]
<?php echo h($message); ?>
</td>
<td>
<span title="<?php echo (isset($context['file']) ? $context['file'] : '') ?>">
<?php echo (isset($context['file']) ? basename($context['file']) : '') ?>
<?php echo (isset($context['line']) ? ' : ' . $context['line'] : '') ?>
</span>
</td>
<td>
<?php 
$seconds = (int) $record['time'];
$millis = floor(($record['time'] - $seconds) * 1000);
echo date('Y-m-d H:i:s', $seconds) . sprintf('.%03d ', $millis) . date('P');
?>
</td>
</tr>
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

<footer>
<?php echo $app; ?> 
<?php echo $version; ?>
</footer>


</body>
</html>