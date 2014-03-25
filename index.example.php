<?php
/**
 * Main entry-script of Jivoo
 *
 * This file can be used to change the locations of files and directories
 * used by Jivoo. 
 *
 * @package Jivoo
 * @since 0.1.0
 */

require_once 'lib/Jivoo/Core/bootstrap.php';

$app = new App(include 'app/app.php', basename(__FILE__));

// Paths are relative to the current directory (dirname($_SERVER['SCRIPT_FILENAME']))
// unless they begin with '/' or 'x:' where x is any drive letter.
$app->paths->config = 'config';
$app->paths->log = 'log';
$app->paths->tmp = 'tmp';
$app->paths->extensions = 'extensions';
$app->paths->themes = 'themes';

$environment = getenv('APP_ENVIRONMENT');
$environment || $environment = 'production';

$app->run($environment);
