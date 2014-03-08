<?php
/**
 * @package PeanutCMS\Schemas
 */
class TagSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->tag = DataType::string(255);
    $this->name = DataType::string(255);
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
  }
}
