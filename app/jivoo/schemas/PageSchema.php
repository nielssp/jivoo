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
    $this->contentText = DataType::text();
    $this->contentHtml = DataType::text();
    $this->contentFormat = DataType::string(255, false, 'html');
    $this->published = DataType::boolean(false, true);
    $this->addTimeStamps();
    $this->addUnique('name', 'name');
    $this->addIndex('created', 'created');
  }
}
