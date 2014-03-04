<?php
/**
 * Automatically generated schema for Posts table
 * @package PeanutCMS\Schemas
 */
class PostSchema extends Schema {
  protected function createSchema() {
    $this->id = DataType::integer(DataType::AUTO_INCREMENT | DataType::UNSIGNED);
    $this->name = DataType::string(255);
    $this->title = DataType::string(255);
    $this->content = DataType::text();
    $this->state = DataType::string(50);
    $this->status = DataType::string(50);
    $this->commenting = DataType::string(10);
    $this->userId = DataType::integer(DataType::UNSIGNED);
    $this->createdAt = DataType::dateTime();
    $this->updatedAt = DataType::dateTime();
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
}
