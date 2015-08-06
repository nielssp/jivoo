<?php
namespace Blog\Schemas;

use Jivoo\Databases\Schema;
use Jivoo\Models\DataType;

class CommentSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->postId = DataType::integer(DataType::UNSIGNED);
    $this->author = DataType::string(255);
    $this->content = DataType::text();
    $this->addTimeStamps();
    $this->addIndex('postId', 'postId');
  }
}
