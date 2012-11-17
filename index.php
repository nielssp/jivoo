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
define('DEBUG', true);

define('LOG_ERRORS', true);

require_once('lib/bootstrap.php');

$app = new App('app');

$app->run();
