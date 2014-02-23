<?php
/**
 * Automatically generated schema for usersessions table
 * @package PeanutCMS\Schemas
 */
class usersessionsSchema extends Schema {
  protected function createSchema() {
    $this->addString('id', 255, Schema::NOT_NULL);
    $this->addInteger('user_id', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addInteger('valid_until', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->setPrimaryKey('id');
  }
}
