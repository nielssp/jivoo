<?php
/**
 * Automatically generated schema for tags table
 * @package PeanutCMS\Schemas
 */
class tagsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('tag', 255, Schema::NOT_NULL);
    $this->addString('name', 255, Schema::NOT_NULL);
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
  }
}
