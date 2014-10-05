<?php
/**
 * Automatically generated schema for Posts table
 * @package PeanutCMS\Schemas
 */
class PostSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->name = DataType::string(255);
    $this->title = DataType::string(255);
    $this->content = DataType::text();
    $this->contentText = DataType::text();
    $this->contentHtml = DataType::text();
    $this->contentFormat = DataType::string(255, false, 'html');
    $this->status = DataType::enum('PostStatus', false, 'published');
    $this->commenting = DataType::boolean(false, false);
    $this->userId = DataType::integer(DataType::UNSIGNED, true);
    $this->addTimestamps();
    $this->addUnique('name', 'name');
    $this->addIndex('created', 'created');
  }
}
