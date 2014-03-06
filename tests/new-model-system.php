<?php
include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core/Routing');
Lib::import('Core/Models');
Lib::import('Core/Models/Condition');
Lib::import('Core/Models/Selection');
Lib::import('Core/Models/Validation');
Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');
Lib::import('Core/Authentication/default/schemas');

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
  'tablePrefix' => 'dev_',
));


header('Content-Type:text/plain');



var_dump($db->escapeQuery('WHERE {User}.user = ? AND date = %DATE AND listId IN %d()', 5, 'next monday', array(1, 2, 3)));

$User = $db->getTable('User', new UserSchema());

$user = $User->where('createdAt = %d', strtotime('1970-01-01 01:00'))->first();
$user->save();

var_dump(Logger::getLog());