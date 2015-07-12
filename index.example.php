<?php
/**
 * Main entry-script of a Jivoo application
 *
 * Copy to an empty directory and access from webbrowser to create a new
 * application. 
 *
 * @package Jivoo
 * @since 0.1.0
 */

// Points to Jivoo framework
require_once 'lib/Jivoo/Core/bootstrap.php';

$app = new App(
  'app',
  'user',
  basename(__FILE__)
);

// Optional. Application-independent extensions and themes delivered with Jivoo.
$app->paths->share = 'share';

// Run application. Environment can be 'development', 'production' or something
// else. 'development' should ONLY be used on private local development web
// servers.
$app->run('development');
