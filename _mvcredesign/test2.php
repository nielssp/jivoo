<?php
/**
 * Test number 2
 * Purpose: Development of database handler and query builder
 */








$configuration = array(
  'driver' => 'MySql',
  'server' => 'localhost',
  'username' => 'mvctest',
  'password' => 'mvctest',
  'database' => 'mvctest',
  'prefix' => 'pcms_'
);

$db = new Database($configuration);

$db->insertQuery('table')
  ->addColumns('id', 'name')
  ->addValues('2', 'N"i"els')
  ->execute();

$query = $db->rawQuery("SELECT * FROM ? WHERE user = ? AND id = ?");
$query->addTable('table');
$query->addVar('Ad"m"in');
$query->addVar(23);
$query->execute();

// or:

$query = RawQuery::create("SELECT * FROM ? WHERE user = ? AND id = ?");
$query->addTable('table');
$query->addVar('Admin');
$query->addVar(23);
$db->execute($query);

$query = $db->selectQuery('table')
  ->addColumns('id', 'user')
  ->where('user = ? AND id = ?')
  ->addVar('Admin')
  ->addVar(32)
  ->orderBy('id')
  ->limit(30)
  ->offset(2)
  ->execute();





ActiveRecord::addModel('Post', 'posts');

class Post extends ActiveRecord {
  protected $validate = array(
    'title' => array('presence' => true,
                     'minLength' => 4,
                     'maxLength' => 25),
    'content' => array('presence' => true),
  );
}
class Comment extends ActiveRecord {

}


$post = Post::create(array(
  'title' => 'Hello',
  'content' => 'Hello", World'
));
