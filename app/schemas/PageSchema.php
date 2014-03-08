<?php
/**
 * @package PeanutCMS\Schemas
 */
class PageSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->name = DataType::string(255);
    $this->title = DataType::string(255);
    $this->content = DataType::text();
    $this->statue = DataType::string(10);
    $this->addTimeStamps();
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
}
