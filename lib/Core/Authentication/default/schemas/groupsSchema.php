<?php
/**
 * Automatically generated schema for groups table
 * @package Core\Authentication
 */
class groupsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL);
    $this->addString('name', 255, Schema::NOT_NULL);
    $this->addString('title', 255, Schema::NOT_NULL);
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
  }
}
