<?php
/**
 * @package PeanutCMS\Schemas
 */
class CommentSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->postId = DataType::integer(DataType::UNSIGNED);
    $this->userId = DataType::integer(DataType::UNSIGNED);
    $this->parentId = DataType::integer(DataType::UNSIGNED, true);
    $this->author = DataType::string(255);
    $this->email = DataType::string(255);
    $this->website = DataType::string(255);
    $this->content = DataType::text();
    $this->contentText = DataType::text();
    $this->ip = DataType::string(255);
    $this->status = DataType::string(50);
    $this->addTimeStamps();
    $this->setPrimaryKey('id');
    $this->addIndex('postId', 'postId');
  }
}
