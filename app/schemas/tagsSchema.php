<?php
/**
 * Automatically generated schema for tags table
 * @package PeanutCMS
 * @subpackage Schemas
 */
class tagsSchema extends Schema {
  public $id = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'primary',
    'autoIncrement' => true,
    'null' => false,
  );

  public $tag = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $name = array(
    'type' => 'string',
    'length' => 255,
    'key' => 'unique',
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
  );
}
