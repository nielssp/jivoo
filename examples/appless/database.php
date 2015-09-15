<?php
// Example: Using Jivoo database system outside of Jivoo applications.

use Jivoo\Core\Store\Document;
use Jivoo\Databases\DatabaseSchemaBuilder;
use Jivoo\Databases\DynamicSchema;
use Jivoo\Databases\Loader;

// Include Jivoo by either using composer or including the bootstrap script:
require '../../src/bootstrap.php';

// Initialize database loader with connection settings for "default" database:
$loader = new Loader(new Document(array(
  'default' => array(
    'driver' => 'PdoMysql',
    'server' => 'localhost',
    'username' => 'jivoo',
    'database' => 'jivoo'
  )
)));

// Connect to "default":
$db = $loader->connect('default');

// Get data for root user:
echo '<pre>';
print_r($db->User->where('username = %s', 'root')->first()->getData());
echo '</pre>';

// List names of users created after 2015-01-01
$users = $db->User
  ->where('created > %d', strtotime('2015-01-01'))
  ->orderBy('created');

foreach ($users as $user) {
  echo h($user->username) . '</br>';
}
