<?php
// Example: Using Jivoo database system outside of Jivoo applications.

use Jivoo\Databases\Loader;
use Jivoo\Core\Store\Document;

// Include Jivoo by either using composer or including the bootstrap script:
require '../../src/bootstrap.php';

// Create configuration with connection settings for "default" database:
$config = new Document();
$config['default'] = array(
  'driver' => 'PdoMysql',
  'server' => 'localhost',
  'username' => 'jivoo',
  'database' => 'jivoo'
);

// Initialize database loader with the above configuration:
$loader = new Loader($config);

// Connect to "default":
$db = $loader->connect('default');

// Get all usernames:
var_dump($db->User->select('username'));