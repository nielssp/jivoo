<?php
class DatabaseConnection implements IDatabase {
  private $tables = array();
  private $connection;
  private $schema;

  public function __construct(IDatabase $database) {
    $this->connection = $database;
    $this->schema = $database->getSchema();
  }

  public function __get($table) {
    if (isset($this->tables[$table]))
      return $this->tables[$table];
    if (isset($this->connection->$table)) {
      $this->tables[$table] = $this->connection->$table;
      return $this->tables[$table];
    }
    return parent::__get($table);
  }
  
  public function __isset($table) {
    if (isset($this->tables[$table]))
      return true;
    if (isset($this->connection->$table)) {
      $this->tables[$table] = $this->connection->$table;
      return true;
    }
    return false;
  }
  
  public function __set($table, IModel $model) {
    $this->tables[$table] = $model;
  }
  
  public function __unset($table) {
    unset($this->tables[$table]);
  }

  public function getConnection() {
    return $this->connection;
  }

  public function getSchema() {
    return $this->schema;
  }
  
  public function refreshSchema() {
    $this->connection->refreshSchema();
    $this->schema = $this->connection->getSchema();
  }

  public function close() {
    $this->connection->close();
  }
  
  public function beginTransaction() {
    $this->connection->beginTransaction();
  }
  
  public function commit() {
    $this->connection->commit();
  }
  
  public function rollback() {
    $this->connection->rollback();
  }
}
