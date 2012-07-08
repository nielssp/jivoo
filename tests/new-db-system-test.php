<?php
include '../app/essentials.php';

include p(CLASSES . 'database/MysqlDatabase.php');

class postsSchema extends Schema {
  public $id = array(
    'type' => 'integer',
    'key' => 'primary',
    'autoIncrement' => TRUE,
    'null' => FALSE
  );

  public $name = array(
    'type' => 'string',
    'legnth' => 255,
    'key' => 'unique'
  );

  public $title = array(
    'type' => 'string',
    'length' => 255
  );

  public $content = array(
    'type' => 'text'
  );

  public $date = array(
    'type' => 'timestamp',
    'key' => 'index'
  );

  public $comments = array(
    'type' => 'integer'
  );

  public $state = array(
    'type' => 'string'
  );

  public $commenting = array(
    'type' => 'string'
  );

  public $user_id = array(
    'type' => 'integer',
    'key' => 'index'
  );


  public $indexes = array(
    'PRIMARY' => array(
      'columns' => array('id'),
      'unique' => TRUE
    ),
    'name' => array(
      'columns' => array('name'),
      'unique' => TRUE
    ),
    'date' => array(
      'columns' => array('date'),
      'unique' => FALSE
    )
  );
}

echo '<pre>';


$options = array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms'
);

$db = new MysqlDatabase($options);

$table = 'tags';

$schema = $db->getTable($table)->getSchema();

echo '</pre>';
