<?php
// Main entry-script of a Jivoo application.
//
// Copy to an empty directory (as 'index.php') on a local web server and access
// it using a web browser to create a new application. 
//

// Change current working directory to the one containing this file (ensures
// that relative paths work correctly, especially when used from the CLI).
chdir(dirname(__FILE__));

// Path to $directory containing Jivoo framework distribution.
$root = '.';

// Path to Jivoo framework bootstrap script.
require_once $root . '/src/bootstrap.php';

$app = new \Jivoo\Core\App(
  'app', // Path to application directory containing the manifest (app.json).
  'user', // Path to configuration directory.
  basename(__FILE__)
);

// Optional. Application-independent extensions and themes delivered with Jivoo.
$app->paths->share = $root . '/share';

// Run application. Environment can be 'development', 'production' or something
// else (defined in app/config/environments). 'development' should ONLY be used
// on private local development web servers.
$app->run('development');
