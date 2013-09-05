<?php
/**
 * Automatically generated schema for pages table
 * @package PeanutCMS\Schemas
 */
class pagesSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL);
    $this->addString('name', 255, Schema::NOT_NULL);
    $this->addString('title', 255, Schema::NOT_NULL);
    $this->addText('content', Schema::NOT_NULL);
    $this->addInteger('date', Schema::NOT_NULL);
    $this->addString('state', 10, Schema::NOT_NULL);
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
    $this->addIndex('date', 'date');
  }
}
