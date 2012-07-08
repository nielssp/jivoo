<?php
include '../app/essentials.php';

include p(CLASSES . 'database/MysqlDatabase.php');

class postsSchema extends Schema {
  public $id = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'primary',
    'autoIncrement' => true,
    'null' => false,
  );

  public $name = array(
    'type' => 'string',
    'length' => 255,
    'key' => 'unique',
    'null' => false,
  );

  public $title = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $content = array(
    'type' => 'text',
    'null' => false,
  );

  public $date = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'index',
    'null' => false,
  );

  public $comments = array(
    'type' => 'integer',
    'length' => 11,
    'null' => false,
  );

  public $state = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $commenting = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $user_id = array(
    'type' => 'integer',
    'length' => 11,
    'null' => false,
  );

  public $indexes = array(
    'PRIMARY' => array(
      'columns' => array('id'),
      'unique' => true
    ),
    'name' => array(
      'columns' => array('name'),
      'unique' => true
    ),
    'date' => array(
      'columns' => array('date'),
      'unique' => false
    ),
  );
}
echo '<pre>';


$options = array(
  'server' => 'localhost',
  'username' => 'peanutcms-test',
  'password' => 'peanutcms-test',
  'database' => 'peanutcms-testing'
);

$db = new MysqlDatabase($options);


Post::connect($db->posts);

$post = Post::create();
$post->name = '2';
$post->title = 'abcdsf';

var_dump($post->isValid());

var_dump($post->getErrors());

echo '</pre>';
