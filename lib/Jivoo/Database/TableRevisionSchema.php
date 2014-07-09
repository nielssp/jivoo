<?php
class TableRevisionSchema extends Schema {
  protected function createSchema() {
    $this->name = DataType::string(255);
    $this->revision = DataType::integer();
    $this->setPrimaryKey('name');
  }
}