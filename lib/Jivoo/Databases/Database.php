<?php
abstract class Database implements IDatabase, IDatabaseSchema {
  
  protected $Databases;
  
  private $tables = array();
  private $schemas = array();
  
  public final function __construct(Databases $dbs) {
    $this->Databases = $dbs;
    $this->init();
  }
  
  protected function init() { }
  
  /**
   * Connect to default database and attach all schemas found in schemas folder
   */
  protected function attachDefault() {
    $this->connect('default', $this->Databases->getSchemas());
  }
  
  protected function connect($options, $schemas, $name = null) {
    $this->Databases->connect($options, $schemas, $name);
    foreach ($schemas as $schema) {
      if (is_string($schema))
        $schema = $this->Databases->getSchema($schema);
      $this->tables[] = $schema->getName();
      $this->schemas[$schema->getName()] = $schema; 
    }
  }
  
  public function getTables() {
    return $this->tables;
  }
  
  public function getSchema($table = null) {
    if (!isset($table))
      return $this;
    if (isset($this->schemas[$table]))
      return $this->schemas[$table];
    return null;
  }
  
}