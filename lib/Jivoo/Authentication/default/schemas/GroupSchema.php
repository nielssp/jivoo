<?php
/**
 * @package Core\Authentication
 */
class GroupSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->name = DataType::string(255);
    $this->title = DataType::string(255);
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
  }
}
