<?php

date_default_timezone_set('Europe/Copenhagen');
include '../lib/Jivoo/bootstrap.php';

Lib::import('Core');
Lib::import('Jivoo/Routing');
Lib::import('Jivoo/Models');
Lib::import('Jivoo/Models/Condition');
Lib::import('Jivoo/Models/Selection');
Lib::import('Jivoo/Models/Validation');
Lib::import('Jivoo/Database');
Lib::import('Jivoo/Database/Mixins');
Lib::import('Jivoo/Database/PdoMysql');
Lib::import('Jivoo/Authentication/default/schemas');
Lib::import('Jivoo/Authentication/default/models');

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
  'tablePrefix' => 'test_',
));


header('Content-Type:text/plain');

$app = new App(require '../app/app.php', basename(__FILE__));

$app->config['Database'] = array(
  'driver' => 'PdoMysql',
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
  'tablePrefix' => 'test_',
);

$app->loadModule('Jivoo/Database');

var_dump($db->escapeQuery('WHERE {User}.user = ? AND date = %DATE AND listId IN %d()', 5, 'next monday', array(1, 2, 3)));

$User = $db->getTable('User', new UserSchema());
$Group = $db->getTable('Group', new GroupSchema());
$GroupPermission = $db->getTable('GroupPermission', new GroupPermissionSchema());
$Session = $db->getTable('Session', new SessionSchema());

$User = new User($db);
$Group = new Group($db);

$db->addActiveModel($User);
$db->addActiveModel($Group);

$user = $User->first();


$group = $user->group;

echo get_class($group) . PHP_EOL;
echo $group->users->count();

var_dump(Logger::getLog());
