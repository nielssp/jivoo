<?php
// Example: Using Jivoo database system outside of Jivoo applications.

use Jivoo\Databases\Loader;
use Jivoo\Core\Store\Document;
use Jivoo\Databases\DynamicSchema;
use Jivoo\Databases\DatabaseSchemaBuilder;

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

// Get data for first user:
var_dump($db->User->first()->getData());

// List names of users created after 2015-01-01
$users = $db->User
  ->where('created > %d', strtotime('2015-01-01'))
  ->orderBy('created');

foreach ($users as $user) {
  echo h($user->username) . '</br>';
}
