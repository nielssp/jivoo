#!/usr/bin/php
<?php
include 'lib/ApakohPHP/bootstrap.php';

Lib::import('ApakohPHP/Database');
Lib::import('ApakohPHP/Database/Mysql');

$options = array('server' => 'localhost', 'username' => 'peanutcms',
  'password' => 'peanutcms', 'database' => 'peanutcms'
);

$db = new MysqlDatabase($options);

$tables = array('comments', 'groups', 'groups_permissions', 'links', 'pages',
  'posts', 'posts_tags', 'tags', 'users'
);

foreach ($tables as $table) {
  echo 'Generating schema for ' . $table . '...' . PHP_EOL;
  $schema = $db->$table
    ->getSchema();
  $file = fopen('config/schemas/' . $table . 'Schema.php', 'w');
  fwrite($file, $schema->export());
  fclose($file);
}
