<?php

date_default_timezone_set('Europe/Copenhagen');
include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core/Routing');
Lib::import('Core/Models');
Lib::import('Core/Models/Condition');
Lib::import('Core/Models/Selection');
Lib::import('Core/Models/Validation');
Lib::import('Core/Database');
Lib::import('Core/Database/Mixins');
Lib::import('Core/Database/PdoMysql');
Lib::import('Core/Authentication/default/schemas');
Lib::import('Core/Authentication/default/models');

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

$app->loadModule('Core/Database');

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
