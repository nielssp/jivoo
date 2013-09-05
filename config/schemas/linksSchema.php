<?php
/**
 * Automatically generated schema for links table
 * @package PeanutCMS\Schemas
 */
class linksSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('menu', 255, Schema::NOT_NULL);
    $this->addString('type', 10, Schema::NOT_NULL);
    $this->addString('title', 255, Schema::NOT_NULL);
    $this->addText('path', Schema::NOT_NULL);
    $this->addInteger('position', Schema::NOT_NULL, '0');
    $this->setPrimaryKey('id');
    $this->addIndex('menu', 'menu');
  }
}
