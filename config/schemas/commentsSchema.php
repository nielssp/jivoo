<?php
/**
 * Automatically generated schema for comments table
 * @package PeanutCMS\Schemas
 */
class commentsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addInteger('post_id', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addInteger('user_id', Schema::NOT_NULL | Schema::UNSIGNED, '0');
    $this->addInteger('parent_id', Schema::NOT_NULL | Schema::UNSIGNED, '0');
    $this->addString('author', 255, Schema::NOT_NULL);
    $this->addString('email', 255, Schema::NOT_NULL);
    $this->addString('website', 255, Schema::NOT_NULL);
    $this->addText('content', Schema::NOT_NULL);
    $this->addInteger('date', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('ip', 255, Schema::NOT_NULL);
    $this->addString('status', 50, Schema::NOT_NULL);
    $this->addText('content_text', Schema::NOT_NULL);
    $this->setPrimaryKey('id');
    $this->addIndex('post_id', 'post_id');
  }
}
