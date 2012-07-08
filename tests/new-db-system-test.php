<?php
include '../app/essentials.php';

include p(CLASSES . 'database/MysqlDatabase.php');

echo '<pre>';

$options = array(
  'server' => 'localhost',
  'username' => 'peanutcms-test',
  'password' => 'peanutcms-test',
  'database' => 'peanutcms-testing'
);

$db = new MysqlDatabase($options);
var_dump($db->tableExists('posts'));
var_dump($db->tableExists('users'));

var_dump(isset($db->posts));
var_dump(isset($db->users));

var_dump($table = $db->posts);

var_dump($table->getColumns());

var_dump($db->posts->count(SelectQuery::create()->where('name LIKE ?', 'test%')));

var_dump($db->posts->update(UpdateQuery::create()->set('content', 'testing2')->where('name  = ?', 'test2')));

var_dump($db->posts->update()->set('title', 'Test no. 2')->where('name = ?', 'test2')->execute());

echo '</pre>';
