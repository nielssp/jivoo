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
  private $migrations = null;
  
  protected function init() {
    $this->config->defaults = array(
      'force' => false,
      'indicator' => 'version',
      'versions' => array(),
    );
    
    // Initialize SchemaRevision schema
    $this->schema = new Schema('SchemaRevision');
    $this->schema->revision = DataType::string(255);
    $this->schema->setPrimaryKey('revision');
    
    // Check (and migrate) all loaded databases
    foreach ($this->m->Databases->getConnections() as $name => $connection) {
      if ($this->config['indicator'] == 'version') {
        if ($this->app->version == $this->config['versions'][$name])
          continue;
      }
      $this->check($connection);
      $this->config['versions'][$name] = $this->app->version;
    }
  }
  
  /**
   * Get sorted list of migrations
   * @return string[] List of migrations
   */
  public function getMigrations() {
    if (!isset($this->migrations)) {
      $this->migrations = array();
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
        sort($this->migrations);
      }
    }
    return $this->migrations;
  }
  
  /**
   * Check a database for 
   * @param LoadableDatabase $db
   */
  public function check(IMigratableDatabase $db) {
    if (!isset($db->SchemaRevision)) {
      // Create SchemaRevision table if it doesn't exist
      Logger::debug('Creating SchemaRevision table');
      $db->createTable($this->schema);
      foreach ($this->getMigrations() as $migration) 
        $db->SchemaRevision->insert(array('revision' => $migration));
    }
    else {
      // Run necessary migrations
      $currentState = array();
      foreach ($db->SchemaRevision->select('revision') as $row)
        $currentState[$row['revision']] = true;
      $migrations = $this->getMigrations();
      foreach ($migrations as $migration) {
        if (!isset($currentState[$migration])) {
          Logger::debug('Running migration ' . $migration);
          Lib::assumeSubclassOf($migration, 'Migration');
          $object = new $migration($db);
        }
      }
    }
    
    // Create missing tables
    $schema = $db->getSchema();
    foreach ($schema->getTables() as $table) {
      if (!isset($db->$table)) {
        Logger::debug('Missing table "' . $table . '": creating it...');
        $db->createTable($schema->getSchema($table));
      }
    }
  }
  
  public function runMigration(IMigratableDatabase $db, $migration) {
    
  }
}