<?php
abstract class LoadableDatabase extends Module implements IDatabase, IMigratable {
  
  private $schema;
  
  private $tableNames;
  
  private $migrationAdapter;
  
  private $tables;
  
  public final function __construct(App $app, IDatabaseSchema $schema, $options = array()) {
    parent::__construct($app);
    $this->schema = $schema;
    $this->init($options);
    $this->migrationAdapter = $this->getMigrationAdapter();
    $this->tableNames = $this->getTables();
    foreach ($this->tableNames as $table) {
      $this->tables[$table] = $this->getTable($table);
    }
  }
  
  public function __get($table) {
    if (!isset($this->tables[$table])) {
      throw new TableNotFoundException(
        tr('Table not found: "%1"', $table)
      );
    }
    return $this->tables[$table];
  }
  
  public function __isset($table) {
    return isset($this->tables[$table]);
  }
  
  protected abstract function getTable($table);
  
  protected abstract function init($options);
  
  protected abstract function getMigrationAdapter();
  
  public function getSchema() {
    return $this->schema;
  }
  
  public function setSchema(IDatabaseSchema $schema) {
    $this->schema = $schema;
    foreach ($schema->getTables() as $table) {
      $tableSchema = $schema->getSchema($table);
      $this->$table->setSchema($tableSchema);
    }
  }
  
  public function refreshSchema() {
    $this->schema = new DatabaseSchema();
    foreach ($this->tableNames as $table) {
      $schema = $this->getTableSchema($table);
      $this->schema->addSchema($schema);
      $this->$table->setSchema($schema);
    }
  }

  public function getTables() {
    return $this->migrationAdapter->getTables();
  }
  
  public function getTableSchema($table) {
    return $this->migrationAdapter->getTableSchema($table);
  }
  
  public function createTable(Schema $schema) {
    $this->migrationAdapter->createTable($schema);
    $this->schema->addSchema($schema);
  }
  
  public function renameTable($table, $newName) {
    $this->migrationAdapter->renametable($table, $newName);
  }
  
  public function dropTable($table) {
    $this->migrationAdapter->dropTable($table);
  }
  
  public function addColumn($table, $column, DataType $type) {
    $this->migrationAdapter->addColumn($table, $column, $type);
  }
  
  public function deleteColumn($table, $column) {
    $this->migrationAdapter->deleteColumn($table, $column);
  }
  
  public function alterColumn($table, $column, DataType $type) {
    $this->migrationAdapter->alterColumn($table, $column, $type);
  }
  
  public function renameColumn($table, $column, $newName) {
    $this->migrationAdapter->renameColumn($table, $column, $newName);
  }
  
  public function createIndex($table, $index, $options = array()) {
    $this->migrationAdapter->createIndex($table, $index, $options);
  }
  
  public function deleteIndex($table, $index) {
    $this->migrationAdapter->deleteIndex($table, $index);
  }
  
  public function alterIndex($table, $index, $options = array()) {
    $this->migrationAdapter->alterIndex($table, $index, $options);
  }
} 