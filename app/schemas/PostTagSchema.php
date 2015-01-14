<?php
/**
 * @package PeanutCMS\Schemas
 */
class PostTagSchema extends Schema {
  protected function createSchema() {
    $this->postId = DataType::integer(DataType::UNSIGNED);
    $this->tagId = DataType::integer(DataType::UNSIGNED);
    $this->setPrimaryKey('postId', 'tagId');
  }
}
