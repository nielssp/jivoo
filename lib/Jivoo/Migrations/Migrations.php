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
   * @var array Associative array of migration names and objects that need to run
   */
  private $migrations = array();

  private $checkList = array();

  private $migrationDirs = array();
  
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
    

    if (isset($this->app->appConfig['migrations'])) {
      foreach ($this->app->appConfig['migrations'] as $name) {
        $this->attachDatabase($name, $this->p('app', 'schemas/' . $name . '/migrations'));
      }
    }
    else if (isset($this->m->Databases->default)) {
      $this->attachDatabase('default', $this->p('app', 'schemas/migrations'));
    }

    $this->run();
    $this->afterRun();
  }

  public function attachDatabase($name, $migrationDir) {
    $this->migrationDirs[$name] = $migrationDir;
    if ($this->config['indicator'] == 'version') {
      if ($this->app->version != $this->config['versions'][$name])
        $this->check($name);
    }
  }

  public function getMigrations($name) {
    $migrationDir = $this->migrationDirs[$name];
    $migrations = array();
    if (is_dir($migrationDir)) {
      Lib::addIncludePath($migrationDir);
      $files = scandir($migrationDir);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) and $split[1] == 'php') {
            $migrations[] = $split[0];
          }
        }
      }
    }
    return $migrations;
  }

  /**
   * Check a database for 
   * @param LoadableDatabase $db
   */
  public function check($name) {
    $db = $this->m->Databases->$name->getConnection();
    Lib::assumeSubclassOf($db, 'IMigratableDatabase');
    if (!isset($db->SchemaRevision)) {
      // Create SchemaRevision table if it doesn't exist
      Logger::debug('Creating SchemaRevision table');
      $db->createTable($this->schema);
      foreach ($this->getMigrations() as $migration) 
        $db->SchemaRevision->insert(array('revision' => $migration));
    }
    else {
      $db->SchemaRevision->setSchema($this->schema);
      // Schedule necessary migrations
      $currentState = array();
      foreach ($db->SchemaRevision->select('revision') as $row)
        $currentState[$row['revision']] = true;
      $migrations = $this->getMigrations($name);
      foreach ($migrations as $migration) {
        if (!isset($currentState[$migration])) {
          Logger::debug('Initializing migration ' . $migration);
          Lib::assumeSubclassOf($migration, 'Migration');
          $object = new $migration($db);
          $key = $migration . $name;
          $this->migrations[$key] = array($db, $object);
        }
      }
    }
    
    // Create missing tables
    $this->checkList[$name] = $db;
  }
  
  public function run() {
    ksort($this->migrations);
    $log = array();
    try {
      foreach ($this->migrations as $tuple) {
        list($db, $migration) = $tuple;
        try {
          $migration->up();
          $db->SchemaRevision->insert(array('revision' => get_class($migration)));
        }
        catch (Exception $e) {
          $migration->revert();
          throw $e;
        }
      }
    }
    catch (Exception $e) {
      foreach ($log as $migration) {
        $migration->down();
      }
      throw $e;
    }
    $this->migrations = array();
  }

  public function afterRun() {
    foreach ($this->checkList as $name => $db) {
      $schema = $db->getSchema();
      foreach ($schema->getTables() as $table) {
        if (!isset($db->$table)) {
          Logger::debug('Missing table "' . $table . '": creating it...');
          $db->createTable($schema->getSchema($table));
        }
      }
      if ($this->config['indicator'] == 'version')
        $this->config['versions'][$name] = $this->app->version;
    }
    $this->checkList = array();
  }
}
