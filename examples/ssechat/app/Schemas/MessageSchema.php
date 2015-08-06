<?php
namespace Chat\Schemas;

use Jivoo\Databases\Schema;
use Jivoo\Models\DataType;

class MessageSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->author = DataType::string(20, true);
    $this->message = DataType::text();
    $this->addTimeStamps();
    $this->addIndex('created', 'created');
  }
}
