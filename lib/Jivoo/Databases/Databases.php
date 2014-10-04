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
class Databases extends LoadableModule {

  protected $modules = array('Models', 'Helpers');
  
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
    
    if (isset($this->app->appConfig['databases'])) {
      foreach ($this->app->appConfig['databases'] as $name) {
        $this->attachDatabase($name, $this->p('app', 'schemas/' . $name));
      }
    }
    else {
      $this->attachDatabase('default', $this->p('app', 'schemas'));
    }
    
    $schemasDir = $this->p('app', 'schemas');
  }

  public function attachDatabase($name, $schemasDir = null) {
    $schemas = array();
    if (isset($schemasDir) and is_dir($schemasDir)) {
      Lib::addIncludePath($schemasDir);
      $files = scandir($schemasDir);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) AND $split[1] == 'php') {
            $class = $split[0];
            Lib::assumeSubclassOf($class, 'Schema');
            $schemas[] = new $class();
          }
        }
      }
    }
    $this->connect($name, $schemas, $name);
  }
  
  /**
   * (non-PHPdoc)
   * @see Module::__get()
   * @return IModel
   */
  public function __get($name) {
    if (isset($this->connections[$name]))
      return $this->connections[$name];
    return parent::__get($name);
  }
  
  public function __isset($name) {
    return isset($this->connections[$name]);
  }

  public function getConnections() {
    return $this->connections;
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
        $this->connections[$name] = new DatabaseConnection($object);
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
