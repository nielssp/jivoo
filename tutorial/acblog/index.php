<?php
/**
 * Main configuration file and entry-script of an Apakoh Core application
 *
 * @package Acrum
 * @since 0.1.0
 */

/** Locatin of the Aakoh Core bootstrap script */
require_once '../../lib/Core/bootstrap.php';

Lib::import('Core');

$app = new App(include 'app/app.php');

$environment = getenv('ACBLOG_ENVIRONMENT');
$environment || $environment = 'development';

$app->run($environment);
