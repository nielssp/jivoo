<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Core\LoadableModule;

/**
 * Schema and data migration module.
 */
class Migrations extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Databases');
  
  /**
   * @var Schema Table schema for SchemaRevision-table.
   */
  private $schema;
  
  /**
   * @var Migration[] Associative array of migration names and objects that need to run.
   */
  private $migrations = array();
  
  /**
   * @var MigrationSchema[] Array of schemas.
   */
  private $migrationSchemas = array();

  /**
   * @var IMigratableDatabase[] Associative array of names and databases
   * to check.
   */
  private $checkList = array();

  /**
   * @var string[] Associative array of database names and migration dirs.
   */
  private $migrationDirs = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->config->defaults = array(
      'force' => false,
      'indicator' => 'version',
      'versions' => array(),
      'mtimes' => array()
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
    else if ($this->config['indicator'] == 'mtime') {
      if ($this->config['mtimes'][$name] != filemtime($this->migrationDirs[$name] . '/.'))
        $this->check($name);
    }
  }

  /**
   * Find migrations for a database.
   * @param string $name Database name.
   * @return string[] List of migration class names.
   */
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
   * Check a database for schema changes and initialize neccessary migrations.
   * @param string $name Database name.
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
      $refresh = true;
      $migrations = $this->getMigrations($name);
      $migrationSchema = null;
      foreach ($migrations as $migration) {
        if (!isset($currentState[$migration])) {
          if ($refresh) {
            $migrationSchema = new MigrationSchema($db);
            $refresh = false; 
          }
          Logger::debug('Initializing migration ' . $migration);
          Lib::assumeSubclassOf($migration, 'Migration');
          $object = new $migration($db, $migrationSchema);
          $key = $migration . $name;
          $this->migrations[$key] = array($db, $object);
        }
      }
      if (isset($migrationSchema))
        $this->migrationSchemas[] = $migrationSchema;
    }
    
    // Create missing tables
    $this->checkList[$name] = $db;
  }
  
  /**
   * Attempt to run migrations.
   */
  public function run() {
    ksort($this->migrations);
    $log = array();
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
    $this->migrations = array();
  }

  /**
   * Finalizes migration schemas and creates missing tables. Updates indicators.
   */
  public function afterRun() {
    foreach ($this->migrationSchemas as $migrationSchema) {
      $migrationSchema->finalize();
    }
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
      else if ($this->config['indicator'] == 'mtime')
        $this->config['mtimes'][$name] = filemtime($this->migrationDirs[$name] . '/.');
    }
    $this->checkList = array();
  }
}
