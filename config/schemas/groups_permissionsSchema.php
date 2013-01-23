<?php
/**
 * Automatically generated schema for groups_permissions table
 * @package PeanutCMS
 * @subpackage Schemas
 */
class groups_permissionsSchema extends Schema {
  public $group_id = array('type' => 'integer', 'length' => 10,
    'key' => 'primary', 'autoIncrement' => true, 'null' => false,
  );

  public $permission = array('type' => 'string', 'length' => 255,
    'key' => 'primary', 'null' => false,
  );

  public $indexes = array(
    'PRIMARY' => array('columns' => array('group_id', 'permission'),
      'unique' => true
    ),
  );
}
