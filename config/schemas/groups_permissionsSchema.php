<?php
/**
 * Automatically generated schema for groups_permissions table
 * @package PeanutCMS\Schemas
 */
class groups_permissionsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('group_id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('permission', 255, Schema::NOT_NULL);
    $this->setPrimaryKey('group_id', 'permission');
  }
}
