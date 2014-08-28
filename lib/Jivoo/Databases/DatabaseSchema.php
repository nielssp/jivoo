<?php
class DatabaseSchema implements IDatabaseSchema {
  private $schemas = array();
  
  private $tables = array();
  /**
   * 
   * @param Schema[] $schemas
   */
  public function __construct($schemas = array()) {
    foreach ($schemas as $schema)
      $this->addSchema($schema);
  }
  
  public function getTables() {
    return $this->tables;
  }
  
  public function getSchema($table) {
    if (isset($this->schemas[$table]))
      return $this->schemas[$table];
    return null;
  }
  
  public function addSchema(Schema $schema) {
    $name = $schema->getName();
    if (!in_array($name, $this->tables))
      $this->tables[] = $name;
    $this->schemas[$name] = $schema;
  }
}