<?php
/**
 * A wrapper for another database driver.
 */
class DatabaseConnection implements IDatabase {
  /**
   * @var Table[] Tabbles.
   */
  private $tables = array();
  
  /**
   * @var IDatabase Database.
   */
  private $connection;
  
  /**
   * @var IDatabaseSchema Database schema.
   */
  private $schema;

  /**
   * Construct database connection.
   * @param IDatabase $database Database.
   */
  public function __construct(IDatabase $database) {
    $this->connection = $database;
    $this->schema = $database->getSchema();
  }

  /**
   * {@inheritdoc}
   */
  public function __get($table) {
    if (isset($this->tables[$table]))
      return $this->tables[$table];
    if (isset($this->connection->$table)) {
      $this->tables[$table] = $this->connection->$table;
      return $this->tables[$table];
    }
    return parent::__get($table);
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($table) {
    if (isset($this->tables[$table]))
      return true;
    if (isset($this->connection->$table)) {
      $this->tables[$table] = $this->connection->$table;
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function __set($table, IModel $model) {
    $this->tables[$table] = $model;
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($table) {
    unset($this->tables[$table]);
  }

  /**
   * Get wrapped database.
   * @return IDatabase Database.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema;
  }
  
  /**
   * Refresh schema.
   */
  public function refreshSchema() {
    $this->connection->refreshSchema();
    $this->schema = $this->connection->getSchema();
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    $this->connection->close();
  }

  /**
   * {@inheritdoc}
   */
  public function beginTransaction() {
    $this->connection->beginTransaction();
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->connection->commit();
  }

  /**
   * {@inheritdoc}
   */
  public function rollback() {
    $this->connection->rollback();
  }
}
