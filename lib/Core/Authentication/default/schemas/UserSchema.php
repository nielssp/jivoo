<?php
/**
 * @package Core\Authentication
 */
class UserSchema extends Schema {
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->username = DataType::string(255);
    $this->password = DataType::string(255);
    $this->email = DataType::string(255);
    $this->groupId = DataType::integer(DataType::UNSIGNED);
    $this->addTimestamps();
    $this->setPrimaryKey('id');
    $this->addUnique('username', 'username');
    $this->addUnique('email', 'email');
  }
}
