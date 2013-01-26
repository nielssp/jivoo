<?php
/**
 * Automatically generated schema for users table
 * @package PeanutCMS
 * @subpackage Schemas
 */
class usersSchema extends Schema {
  public $id = array(
    'type' => 'integer',
    'unsigned' => true,
    'length' => 10,
    'key' => 'primary',
    'autoIncrement' => true,
    'null' => false,
  );

  public $username = array(
    'type' => 'string',
    'length' => 255,
    'key' => 'unique',
    'null' => false,
  );

  public $password = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $email = array(
    'type' => 'string',
    'length' => 255,
    'key' => 'unique',
    'null' => false,
  );

  public $session = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $cookie = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $ip = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $group_id = array(
    'type' => 'integer',
    'unsigned' => true,
    'length' => 10,
    'null' => false,
  );

  public $indexes = array(
    'PRIMARY' => array(
      'columns' => array('id'),
      'unique' => true
    ),
    'username' => array(
      'columns' => array('username'),
      'unique' => true
    ),
    'email' => array(
      'columns' => array('email'),
      'unique' => true
    ),
  );
}
