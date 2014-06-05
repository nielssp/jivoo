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
    $this->status = DataType::enum('PostStatusEnum', false, 'published');
    $this->commenting = DataType::boolean(false, true);
    $this->userId = DataType::integer(DataType::UNSIGNED, true);
    $this->addTimestamps();
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
}
