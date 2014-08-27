<?php
class Migration {
  
  private $db = null;
  
  private $operations = array();
  
  public final function __construct(MigratableDatabase $db) {
    $this->db = $db;
  }
  
  protected function getTable($table) {
    
  }
  
  protected function createTable(Schema $schema) {
    
  }

  protected function dropTable($table) {
    
  }

  protected function addColumn($table, $column, DataType $type) {
    
  }

  protected function deleteColumn($table, $column) {
    
  }

  protected function alterColumn($table, $column, DataType $type) {
    
  }
  
  protected function renameColumn($table, $column, $newName) {
    
  }

  protected function createIndex($table, $index, $options = array()) {
    
  }

  protected function deleteIndex($table, $index) {
    
  }

  protected function alterIndex($table, $index, $options = array()) {
    
  }
  
  public final function revert() {
    $operations = array_reverse($this->operations);
  }
  
  public function up() {
    $operations = $this->change();
    foreach ($operations as $operation) {
      //...
    }
  }
  
  public function down() {
    $operations = array_reverse($this->change());
    foreach ($operations as $operation) {
      //...
    }
  }
  
  public function change() {
    return array();
  }
}