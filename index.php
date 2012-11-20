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

require_once 'lib/bootstrap.php';

$app = new App(include 'app/app.php');

$app->configPath = 'config';

$environment = getenv('ARACHIS_ENVIRONMENT') || 'production';

$app->run($environment);
