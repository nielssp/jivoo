<?php
/**
 * Automatically generated schema for posts table
 */
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
    'length' => 10,
    'null' => false,
  );

  public $state = array(
    'type' => 'string',
    'length' => 50,
    'null' => false,
  );

  public $status = array(
    'type' => 'string',
    'length' => 50,
    'null' => false,
  );

  public $commenting = array(
    'type' => 'string',
    'length' => 10,
    'null' => false,
  );

  public $user_id = array(
    'type' => 'integer',
    'length' => 10,
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

  public function addColumn_status(IDatabase $db) {
    $db->addColumn('posts', 'status', $this->status);
    $db->posts->update()->set('status = state')->execute();
  }
}
