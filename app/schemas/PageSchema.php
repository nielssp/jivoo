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
    $this->published = DataType::boolean(false, true);
    $this->addTimeStamps();
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
}
