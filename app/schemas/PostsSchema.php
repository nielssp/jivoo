<?php
/**
 * Automatically generated schema for Posts table
 * @package PeanutCMS\Schemas
 */
class PostsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('name', 255, Schema::NOT_NULL);
    $this->addString('title', 255, Schema::NOT_NULL);
    $this->addText('content', Schema::NOT_NULL);
    $this->addInteger('date', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addInteger('comments', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('state', 50, Schema::NOT_NULL);
    $this->addString('commenting', 10, Schema::NOT_NULL);
    $this->addInteger('userId', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('status', 50, Schema::NOT_NULL);
    $this->setPrimaryKey('id');
    $this->addUnique('name', 'name');
    $this->addIndex('date', 'date');
  }
}
