#!/usr/bin/php
<?php
include 'essentials.php';

include p(CLASSES . 'database/MysqlDatabase.php');

$options = array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms'
);

$db = new MysqlDatabase($options);

$tables = array(
  'comments',
  'groups',
  'groups_permissions',
  'links',
  'pages',
  'posts',
  'posts_tags',
  'tags',
  'users'
);

foreach ($tables as $table) {
  echo 'Generating schema for ' . $table . '...' . PHP_EOL;
  $schema = $db->$table->getSchema();
  $file = fopen(p(SCHEMAS . $table . 'Schema.php'), 'w');
  fwrite($file, $schema->export());
  fclose($file);
}
  
