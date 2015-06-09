<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Core\LoadableModule;
use Jivoo\Databases\Schema;
use Jivoo\Models\DataType;
use Jivoo\Core\Lib;
use Jivoo\Core\Logger;
use Jivoo\Databases\IMigratableDatabase;

/**
 * Schema and data migration module.
 */
class Migrations extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Databases');
  
  /**
   * @var IMigratableDatabase[] Database connections.
   */
  private $connections = array();
  
  /**
   * @var Schema Table schema for SchemaRevision-table.
   */
  private $schema;
  
  /**
   * @var MigrationSchema[] Array of schemas.
   */
  private $migrationSchemas = array();

  /**
   * @var string[] Associative array of database names and migration dirs.
   */
  private $migrationDirs = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->config->defaults = array(
      'automigrate' => false,
      'silent' => false,
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
  }
  
  /**
   * Get an attached database.
   * @param string $name Database name.
   * @throws \Exception If the database is not attached.
   * @return IMigratableDatabase Database/
   */
  public function getDatabase($name) {
    if (isset($this->connections[$name]))
      return $this->connections[$name];
    throw new \Exception(tr('"%1 is not a migratable database', $name));
  }

  /**
   * Attach a database for migrations.
   * @param string $name Database name.
   * @param string $migrationDir Location of migrations.
   */
  public function attachDatabase($name, $migrationDir) {
    $db = $this->m->Databases->$name->getConnection();
    assume($db instanceof IMigratableDatabase);
    $this->migrationDirs[$name] = $migrationDir;
    $this->connections[$name] = $db;
    if ($this->config['automigrate']) {
      if (!$this->m->Setup->isActive() and is_dir($this->migrationDirs[$name])) {
        $mtime = filemtime($this->migrationDirs[$name] . '/.');
        if (!isset($this->config['mtimes'][$name]) or $this->config['mtimes'][$name] != $mtime) {
          if ($this->config['silent']) {
            $missing = $this->check($name);
            foreach ($missing as $migration)
              $this->run($name, $migration);
            $this->finalize($name);
          }
          else {
            $this->m->Setup->trigger('Jivoo\Migrations\MigrationUpdater');
          }
        }
      }
    }
  }
  
  /**
   * Whether or not the SchemaRevision table has been created.
   * @param string $name Database name.
   * @return bool True if initialized, false otherwise.
   */
  public function isInitialized($name) {
    return isset($this->getDatabase($name)->SchemaRevision);
  }
  
  /**
   * Whether or not a database contains (conflicting) tables already.
   * @param string $name Database name.
   * @return bool True if conflicting tables found.
   */
  public function isClean($name) {
    $db = $this->getDatabase($name);
    if (isset($db->SchemaRevision))
      return false;
    $schema = $db->getSchema();
    foreach ($schema->getTables() as $table) {
      if (isset($db->$table))
        return false;
    }
    return true;
  }
  
  /**
   * Remove all tables of a database including the SchemaRevision table.
   * @param string $name Database name.
   */
  public function clean($name) {
    $db = $this->getDatabase($name);
    if (isset($db->SchemaRevision))
      $db->dropTable('SchemaRevision');
    $schema = $db->getSchema();
    foreach ($schema->getTables() as $table) {
      if (isset($db->$table))
        $db->dropTable($table);
    }
  }
  
  /**
   * Initialize a database for migrations by creating the SChemaRevision table.
   * @param string $name Database name.
   */
  public function initialize($name) {
    $db = $this->getDatabase($name);
    Logger::debug('Creating SchemaRevision table for ' . $name);
    $db->createTable($this->schema);
    foreach ($this->getMigrations($name) as $migration) 
      $db->SchemaRevision->insert(array('revision' => $migration));
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
      Lib::import($migrationDir);
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
    sort($migrations);
    return $migrations;
  }

  /**
   * Check a database for schema changes and initialize neccessary migrations.
   * @param string $name Database name.
   * @return string[] Names of migrations that need to run.
   */
  public function check($name) {
    $db = $this->getDatabase($name);
    $db->SchemaRevision->setSchema($this->schema);
    // Schedule necessary migrations
    $currentState = array();
    foreach ($db->SchemaRevision->select('revision') as $row)
      $currentState[$row['revision']] = true;
    $migrations = $this->getMigrations($name);
    $missing = array();
    foreach ($migrations as $migration) {
      if (!isset($currentState[$migration]))
        $missing[] = $migration;
    }
    return $missing;
  }
  
  /**
   * Run a migration on a database. Will attempt to revert if migration fails.
   * @param string $dbName Name of database.
   * @param string $migrationName Name of migration.
   * @throws Exception If migration fails.
   */
  public function run($dbName, $migrationName) {
    $db = $this->getDatabase($dbName);
    Logger::debug('Initializing migration ' . $migrationName);
    Lib::assumeSubclassOf($migrationName, 'Jivoo\Migrations\Migration');

    // The migration schema keeps track of the state of the database
    if (!isset($this->migrationSchemas[$dbName]))
      $this->migrationSchemas[$dbName] = new MigrationSchema($db);
    $migrationSchema = $this->migrationSchemas[$dbName]; 

    $migration = new $migrationName($db, $migrationSchema);
    try {
      $migration->up();
      $db->SchemaRevision->insert(array('revision' => $migrationName));
    }
    catch (\Exception $e) {
      $migration->revert();
      throw new \Exception(tr('Migration failed: ' . $migrationName), null, $e);
    }
  }
  
  public function finalize($name) {
    if (isset($this->migrationSchemas[$name]))
      $this->migrationSchemas[$name]->finalize();
    $mtime = filemtime($this->migrationDirs[$name] . '/.');
    $this->config['mtimes'][$name] = $mtime;
  }
}
