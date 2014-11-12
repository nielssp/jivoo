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

$appName = 'adk';

require_once 'lib/Jivoo/Core/bootstrap.php';

$app = new App(
  'app/' . $appName,
  'user/' . $appName,
  basename(__FILE__)
);

$app->paths->share = 'share';

$app->run('development');
