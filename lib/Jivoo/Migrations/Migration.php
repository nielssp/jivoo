<?php
abstract class Migration {
  
  private $db = null;
  
  private $operations = array();
  
  public final function __construct(IMigratableDatabase $db) {
    $this->db = $db;
  }
  
  protected function __get($table) {
    return $this->db->$table;
  }

  protected function __isset($table) {
    return isset($this->db->table);
  }
  
  protected function createTable(Schema $schema) {
    $this->db->createTable($schema);
  }

  protected function dropTable($table) {
    $this->db->dropTable($table); 
  }

  protected function addColumn($table, $column, DataType $type) {
    $this->db->addColumn($table, $column, $type); 
  }

  protected function deleteColumn($table, $column) {
    $this->db->deleteColumn($table, $column); 
  }

  protected function alterColumn($table, $column, DataType $type) {
    $this->db->alterColumn($table, $column, $type); 
  }
  
  protected function renameColumn($table, $column, $newName) {
    $this->db->renameColumn($table, $column, $newName); 
  }

  protected function createIndex($table, $index, $options = array()) {
    $this->db->createIndex($table, $index, $options); 
  }

  protected function deleteIndex($table, $index) {
    $this->db->deleteIndex($table, $index); 
  }

  protected function alterIndex($table, $index, $options = array()) {
    $this->alterIndex($table, $index, $options); 
  }
  
  public final function revert() {
    throw new Exception('unsupported');
  }

  public abstract function up();

  public abstract function down();
  
  //public function up() {
    //$operations = $this->change();
    //foreach ($operations as $operation) {
      //$this->do($operation);
    //}
  //}
  
  //public function down() {
    //$operations = array_reverse($this->change());
    //foreach ($operations as $operation) {
      //$this->undo($operation);
    //}
  //}
  
  protected function change() {
    return array();
  }
}
