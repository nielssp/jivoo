<?php
// Module
// Name           : Migrations
// Description    : The Jivoo schema and data migration system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Databases
// After          : Databases
// Before         : ActiveModels

/**
 * Migration module
 * @package Jivoo\Migrations
 */
class Migrations extends LoadableModule {

  protected $modules = array('Databases');
  
  /**
   * @var Schema Table schema for SchemaRevision-table
   */
  private $schema;
  
  /**
   * @var string[] List of migration names
   */
  private $migrations = array();
  
  protected function init() {
    // Initialize SchemaRevision schema
    $this->schema = new Schema('SchemaRevision');
    $this->schema->revision = DataType::string(255);
    $this->schema->setPrimaryKey('revision');
    
    // Load list of migration names
    $migrationsDir = $this->p('app', 'schemas/migrations');
    if (is_dir($migrationsDir)) {
      Lib::addIncludePath($migrationsDir);
      $files = scandir($migrationsDir);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) and $split[1] == 'php') {
            $this->migrations[] = $split[0];
          }
        }
      }
    }
    
    // Check (and migrate) all loaded databases
    foreach ($this->m->Databases->getConnections() as $name => $connection) {
      $this->check($connection);
    }
  }
  
  /**
   * Check a database for 
   * @param LoadableDatabase $db
   */
  public function check(LoadableDatabase $db) {
    // Create SchemaRevision table if it doesn't exist
    if (!isset($db->SchemaRevision)) {
      Logger::debug('Creating SchemaRevision table');
      $db->createTable($this->schema);
      foreach ($this->migrations as $migration) 
        $db->SchemaRevision->insert(array('revision' => $migration));
    }
    
    // Run necessary migrations
    
    // Create missing tables
    $schema = $db->getSchema();
    foreach ($schema->getTables() as $table) {
      if (!isset($db->$table)) {
        Logger::debug('Missing table "' . $table . '": creating it...');
        $db->createTable($schema->getSchema($table));
      }
    }
  }
  
  public function migrate() {
    
  }
}