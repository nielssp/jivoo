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
  
  private $schemas = array();
  
  private $databaseSchemeClasses = array();
  
  private $drivers = null;
  private $connections = array();
  
  protected function init() {
    $this->drivers = new DatabaseDriversHelper($this->app);
    
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
   
  public function __get($database) {
    if (isset($this->connections[$database]))
      return $this->connections[$database];
    return parent::__get($database);
  }
  
  public function __isset($database) {
    return isset($this->connections[$database]);
  }
  
  public function addSchema(Schema $schema) {
    $name = $schema->getName();
    $this->schemas[$name] = $schema;
  }
  
  public function hasSchema($name) {
    return isset($this->schemas[$name]);
  }
  
  public function getSchema($name) {
    if (isset($this->schemas[$name]))
      return $this->schemas[$name];
    return null;
  }
  
  public function getSchemas() {
    return $this->schemas;
  }
  
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
      if (isset($name))
        $this->connections[$name] = $object;
      foreach ($dbSchema->getTables() as $table)
        $this->m->Models->setModel($table, $object->$table);
      return $object;
    }
    catch (DatabaseConnectionFailedException $exception) {
      throw new DatabaseConnectionFailedException(
        tr('Database connection failed: %1', $exception->getMessage())
      );
    }
  }
}

class DatabaseNotConfiguredException extends Exception { }

class DatabaseMissingSchemaException extends Exception { }