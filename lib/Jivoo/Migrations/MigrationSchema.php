<?php
class MigrationSchema implements IDatabaseSchema, IMigratable {
  
  private $targetSchema;
  
  private $db;
  
  /** @var Schema[] List of schemas */
  private $schemas = array();
  
  private $tables = array();
  /**
   * 
   * @param Schema[] $schemas
   */
  public function __construct(IMigratableDatabase $db) {
    $this->db = $db;
    $this->targetSchema = $db->getSchema();
    $db->refreshSchema();
    $current = $db->getSchema();
    foreach ($current->getTables() as $table) {
      $this->tables[] = $table;
      $this->schemas[$table] = $current->getSchema($table);
    }
    $db->setSchema($this);
  }
  
  public function finalize() {
    $this->db->setSchema = $this->targetSchema;
  }
  
  private function reload() {
    $this->db->setSchema($this);
  }
  
  public function getTables() {
    return $this->tables;
  }
  
  public function getSchema($table) {
    if (isset($this->schemas[$table]))
      return $this->schemas[$table];
    return null;
  }
  
  public function createTable(Schema $schema) {
    $table = $schema->getName();
    $this->tables[] = $table;
    $this->schemas[$table] = $schema;
    $this->reload();
  }
  
  public function renameTable($table, $newName) {
    $this->tables = array_diff($this->tables, array($table));
    $schema = $this->schemas[$table];
    // TODO: Change name somehow...
    unset($this->schemas[$table]);
    $this->schemas[$newName] = $schema;
    $this->reload();
  }
  
  public function dropTable($table) {
    $this->tables = array_diff($this->tables, array($table));
    unset($this->schemas[$table]);
    $this->reload();
  }
  
  public function addColumn($table, $column, DataType $type) {
    $this->schemas[$table]->$column = $type;
  }
  
  public function deleteColumn($table, $column) {
    unset($this->schemas[$table]->$column);
  }
  
  public function alterColumn($table, $column, DataType $type) {
    $this->schemas[$table]->$column = $type;
  }
  
  public function renameColumn($table, $column, $newName) {
    $type = $this->schemas[$table]->$column;
    unset($this->schemas[$table]->$column);
    $this->schemas[$table]->$newName = $type;
  }
  
  public function createIndex($table, $index, $options = array()) {
    if ($options['unique'])
      $this->schemas[$table]->addUnique($index, $options['columns']);
    else
      $this->schemas[$table]->addIndex($index, $options['columns']);
  }
  
  public function deleteIndex($table, $index) {
    $this->schemas[$table]->removeIndex($index);
  }
  
  public function alterIndex($table, $index, $options = array()) {
    $this->delteIndex($table, $index);
    $this->createIndex($table, $index, $options);
  }
}