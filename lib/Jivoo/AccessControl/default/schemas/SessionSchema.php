<?php
/**
 */
class SessionSchema extends Schema {
  protected function createSchema() {
    $this->id = DataType::string(255);
    $this->userId = DataType::integer(DataType::UNSIGNED);
    $this->validUntil = DataType::dateTime();
    $this->setPrimaryKey('id');
  }
}
