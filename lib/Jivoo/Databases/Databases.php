<?php
// Module
// Name           : Databases
// Description    : The Jivoo database system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Models Jivoo/Helpers

Lib::import('Jivoo/Databases/Common');

/**
 * Database module
 * @package Jivoo\Databases
 */
class Databases extends LoadableModule implements IDatabase {

  protected $modules = array('Models', 'Helpers');

  /**
   * @var DatabaseSchema
   */
  private $schema = null;
  
  /**
   * @var Schema[]
   */
  private $schemas = array();
  
  /**
   * @var IModel[]
   */
  private $tables = array();
  
  /**
   * @var string[]
   */
  private $databaseSchemeClasses = array();
  
  /**
   * @var DatabaseDriversHelper
   */
  private $drivers = null;
  
  /**
   * @var LodableDatabase[]
   */
  private $connections = array();
  
  protected function init() {
    $this->drivers = new DatabaseDriversHelper($this->app);
    
    $this->schema = new DatabaseSchema();
    
    $schemasDir = $this->p('app', 'schemas');
    if (is_dir($schemasDir)) {
      Lib::addIncludePath($schemasDir);
      $files = scandir($schemasDir);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) AND $split[1] == 'php') {
            $class = $split[0];
            if (is_subclass_of($class, 'Database')) {
              $this->databaseSchemeClasses[] = $class;
            }
            else {
              Lib::assumeSubclassOf($class, 'Schema');
              $this->addSchema(new $class());
            }
          }
        }
      }
    }
  }
  
  public function afterLoad() {
    foreach ($this->databaseSchemeClasses as $class) {
      $object = new $class($this->app);
    }
  } 
  
  /**
   * (non-PHPdoc)
   * @see Module::__get()
   * @return IModel
   */
  public function __get($table) {
    if (isset($this->tables[$table]))
      return $this->tables[$table];
    foreach ($this->connections as $connection) {
      if (isset($connection->$table)) {
        $this->tables[$table] = $connection->$table;
        return $this->tables[$table];
      }
    }
    return parent::__get($table);
  }
  
  public function __isset($table) {
    if (isset($this->tables[$table]))
      return true;
    foreach ($this->connections as $connection) {
      if (isset($connection->$table)) {
        $this->tables[$table] = $connection->$table;
        return true;
      }
    }
    return false;
  }
  
  public function __set($table, IModel $model) {
    $this->tables[$table] = $model;
  }
  
  public function __unset($table) {
    unset($this->tables[$table]);
  }
  
  /**
   * Get current database connections
   * @return LoadableDatabase[] An associative array of connection-name and
   * object
   */
  public function getConnections() {
    return $this->connections;
  }
  
  /**
   * Get an active database connection
   * @param string $database Database name (e.g. "default")
   * @return LodableDatabase|null A database or null if not loaded
   */
  public function getConnection($database) {
    if (isset($this->connections[$database]))
      return $this->connections[$database];
    return null;
  }
  
  /**
   * Check if a database connection exists
   * @param string $database Database name
   * @return bool True if connection exists
   */
  public function hasConnection($database) {
    return isset($this->connections[$database]);
  }
  
  /**
   * Add a table schema
   * @param Schema $schema Table schema
   */
  public function addSchema(Schema $schema) {
    $name = $schema->getName();
    $this->schemas[$name] = $schema;
  }
  
  /**
   * Check if a schema exists
   * @param string $name Schema/table name
   * @return bool True if schema exists
   */
  public function hasSchema($name) {
    return isset($this->schemas[$name]);
  }
  
  /**
   * Get schema of a table or combined database schema
   * @see IDatabase::getSchema()
   */
  public function getSchema($name = null) {
    if (!isset($name))
      return $this->schema;
    if (isset($this->schemas[$name]))
      return $this->schemas[$name];
    return null;
  }
  
  /**
   * Get schemas
   * @return Schema[] Associative array of schema/table names and schemas
   */
  public function getSchemas() {
    return $this->schemas;
  }
  
  /**
   * Make a database connection
   * @param array $options Associative array of database settings
   * @param (string|Schema)[] $schemas An array of table/schema-names and
   * schemas to be attached to the database 
   * @param string $name An optional name for database connection, if the name
   * is provided, the connection and the associated tables will be added to 
   * this Databases-object 
   * @throws DatabaseNotConfiguredException If the $options-array does not
   * contain the necessary information for a connection to be made
   * @throws DatabaseMissingSchemaException If one of the schema names listed
   * in the $schemas-parameter is unknown
   * @throws DatabaseConnectionFailedException If the connection fails
   * @return LoadableDatabase A database object
   */
  public function connect($options, $schemas, $name = null) {
    if (is_string($options)) {
      $name = $options;
      if (!isset($this->config[$name])) {
        throw new DatabaseNotConfiguredException(
          tr('Database "%1" not configured', $name)
        );
      }
      $options = $this->config[$name];
    }
    $driver = $options['driver'];
    $driverInfo = $this->drivers->checkDriver($driver);
    foreach ($driverInfo['requiredOptions'] as $option) {
      if (!isset($options[$option])) {
        throw new DatabaseNotConfiguredException(
          tr('Database option missing: "%1"', $option)
        );
      }
    }
    Lib::import('Jivoo/Databases/Drivers/' . $driver);
    try {
      $class = $driver . 'Database';
      Lib::assumeSubclassOf($class, 'LoadableDatabase');
      $dbSchema = new DatabaseSchema();
      foreach ($schemas as $schema) {
        if (is_string($schema)) {
          $name = $schema;
          $schema = $this->getSchema($name);
          if (!isset($schema)) {
            throw new DatabaseMissingSchemaException(
              tr('Missing schema: "%1"', $name)
            );
          }
        }
        $dbSchema->addSchema($schema);
      }
      $object = new $class($this->app, $dbSchema, $options);
      if (isset($name)) {
        $this->connections[$name] = $object;
      }
      return $object;
    }
    catch (DatabaseConnectionFailedException $exception) {
      throw new DatabaseConnectionFailedException(
        tr('Database connection failed: %1', $exception->getMessage())
      );
    }
  }
  
  public function close() {
    foreach ($this->connections as $connection)
      $connection->close();
  }
  
  public function beginTransaction() {
    foreach ($this->connections as $connection)
      $connection->beginTransaction();
  }
  
  public function commit() {
    foreach ($this->connections as $connection)
      $connection->commit();
  }
  
  public function rollback() {
    foreach ($this->connections as $connection)
      $connection->rollback();
  }
}

/**
 * Invalid database configuration
 */
class DatabaseNotConfiguredException extends Exception { }

/**
 * Unknown table schema 
 */
class DatabaseMissingSchemaException extends Exception { }