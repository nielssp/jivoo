<?php
/**
 * @package PeanutCMS\Schemas
 */
class LinkSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->menu = DataType::string(255, false, 'main');
    $this->type = DataType::string(10);
    $this->title = DataType::string(255);
    $this->path = DataType::text();
    $this->position = DataType::integer(0, false, 0);
    $this->addIndex('menu', 'menu');
  }
}
