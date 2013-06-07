<?php
/**
 * Main entry-script of PeanutCMS
 *
 * This file can be used to change the locations of files and directories
 * used by PeanutCMS. 
 *
 * @package PeanutCMS
 * @since 0.1.0
 */

/** @deprecated */
define('CACHING', true);

/** @deprecated */
define('TMP', '/tmp/peanutcms/');

require_once 'lib/Core/bootstrap.php';

Lib::import('Core');

$app = new App(include 'app/app.php');

// Temporary work-around for weird SCRIPT_NAME.
// When url contains a trailing dot such as
// /PeanutCMS/index.php/admin./something
// SCRIPT_NAME returns /PeanutCMS/index.php/admin./something instead of expected
// /PeanutCMS/index.php
$name = basename(__FILE__);
$script = explode('/', $_SERVER['SCRIPT_NAME']);
while ($script[count($script) - 1] != $name) {
  array_pop($script);
}
$app->basePath = dirname(implode('/', $script));
// END work-around

// Paths are relative to the current directory (dirname($_SERVER['SCRIPT_FILENAME']))
// unless they begin with '/' or 'x:' where x is any drive letter.
$app->paths->config = 'config';
$app->paths->schemas = 'config/schemas';
$app->paths->log = 'log';
$app->paths->tmp = '/tmp/peanutcms';
$app->paths->extensions = 'extensions';
$app->paths->themes = 'themes';

$environment = getenv('APP_ENVIRONMENT');
$environment || $environment = 'production';

//$app->run($environment);
$app->run('development');
