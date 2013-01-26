<?php
/**
 * Automatically generated schema for pages table
 * @package PeanutCMS
 * @subpackage Schemas
 */
class pagesSchema extends Schema {
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

  public $state = array(
    'type' => 'string',
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
}
