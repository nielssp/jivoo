<?php
/**
 * Main configuration file and entry-script of PeanutCMS
 *
 * This file can be used to change PeanutCMS constants.
 * (Available constants can be found in app/constants.php)
 *
 * @package PeanutCMS
 * @since 0.1.0
 */

define('CACHING', true);

define('TMP', '/tmp/peanutcms/');

require_once 'lib/ApakohPHP/bootstrap.php';

Lib::import('ApakohPHP');

$app = new App(include 'app/app.php');

$app->paths->config = 'config';
$app->paths->log = 'log';
$app->paths->tmp = '/tmp/peanutcms';

$environment = getenv('APAKOHPHP_ENVIRONMENT') || 'production';

$app->run($environment);
