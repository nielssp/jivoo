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
    $this->state = DataType::string(50);
    $this->status = DataType::string(50);
    $this->commenting = DataType::string(10);
    $this->userId = DataType::integer(DataType::UNSIGNED);
    $this->addTimestamps();
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
}
