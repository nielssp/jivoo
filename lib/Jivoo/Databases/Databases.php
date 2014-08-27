<?php
// Module
// Name           : Databases
// Description    : The Jivoo database system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Models

/**
 * Database module
 * @package Jivoo\Databases
 */
class Databases extends LoadableModule {

  protected $modules = array('Models');
  
  private $drivers = null;
  private $connections = array();
  
  protected function init() {
    $this->drivers = new DatabaseDriversHelper($this->app);
    if (isset($this->app->appConfig['databases']))
      $databases = $this->app->appConfig['databases'];
    else
      $databases = array('default');
    foreach ($databases as $database) {
      if (!isset($this->config[$database])) {
        throw new DatabaseNotConfiguredException(
          tr('Database "%1" not configured', $database)
        );
      }
      $this->connections[$database] = $this->connect($this->config[$database]);
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
  
  
  public function connect($options) {
    $driver = $options['driver'];
    $driverInfo = $this->drivers->checkDriver($driver);
    foreach ($driverInfo['requiredOptions'] as $option) {
      if (!isset($options[$option])) {
        throw new DatabaseNotConfiguredException(
          tr('Database option missing: "%1"', $option)
        );
      }
    }
    Lib::import('Jivoo/Database/' . $driver);
    try {
      $class = $driver . 'Database';
      Lib::assumeSubclassOf($class, 'LoadableDatabase');
      return new $class($this->app, $options);
    }
    catch (DatabaseConnectionFailedException $exception) {
      throw new DatabaseConnectionFailedException(
        tr('Database connection failed: %1', $exception->getMessage())
      );
    }
  }
}