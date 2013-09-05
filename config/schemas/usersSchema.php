<?php
/**
 * Automatically generated schema for users table
 * @package PeanutCMS\Schemas
 */
class usersSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('username', 255, Schema::NOT_NULL);
    $this->addString('password', 255, Schema::NOT_NULL);
    $this->addString('email', 255, Schema::NOT_NULL);
    $this->addString('session', 255, Schema::NOT_NULL);
    $this->addString('cookie', 255, Schema::NOT_NULL);
    $this->addString('ip', 255, Schema::NOT_NULL);
    $this->addInteger('group_id', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->setPrimaryKey('id');
    $this->addUnique('username', 'username');
    $this->addUnique('email', 'email');
  }
}
