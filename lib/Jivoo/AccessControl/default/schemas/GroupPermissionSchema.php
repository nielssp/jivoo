<?php
/**
 * @package Jivoo\Authentication
 */
class GroupPermissionSchema extends Schema {
  protected function createSchema() {
    $this->groupId = DataType::integer(DataType::UNSIGNED);
    $this->permission = DataType::string(255);
    $this->setPrimaryKey('groupId', 'permission');
  }
}
