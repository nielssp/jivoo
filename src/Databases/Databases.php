<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Utilities;

/**
 * Database module.
 */
class Databases extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected static $loadAfter = array('Setup');
  
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Models', 'Helpers');
  
  /**
   * @var DatabaseDriversHelper Driver helper.
   */
  private $drivers = null;
  
  /**
   * @var LodableDatabase[] Named database connections.
   */
  private $connections = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->drivers = new DatabaseDriversHelper($this->app);
    
    if (isset($this->app->manifest['databases'])) {
      foreach ($this->app->manifest['databases'] as $name) {
        $this->attachDatabase($name, $this->p('app/Schemas/' . $name));
      }
    }
    else {
      $this->attachDatabase('default', $this->p('app/Schemas'));
    }
  }

  /**
   * Attach a database connection.
   * @param string $name Name of database.
   * @param string $schemasDir Location of schema classes.
   */
  public function attachDatabase($name, $schemasDir = null) {
    $schemas = array();
    if (isset($schemasDir) and is_dir($schemasDir)) {
      $files = scandir($schemasDir);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) AND $split[1] == 'php') {
            $class = $this->app->n('Schemas\\' . $split[0]);
            Utilities::assumeSubclassOf($class, 'Jivoo\Databases\Schema');
            $schemas[] = new $class();
          }
        }
      }
    }
    $this->connect($name, $schemas, $name);
  }

  /**
   * Get a database connection.
   * @param string $name Connection name.
   * @return LoadableDatabase Database.
   */
  public function __get($name) {
    if (isset($this->connections[$name]))
      return $this->connections[$name];
    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($name) {
    return isset($this->connections[$name]);
  }

  /**
   * Get all database connections.
   * @return LoadableDatabase[] Associative array of database names and
   * connections.
   */
  public function getConnections() {
    return $this->connections;
  }
  
  /**
   * Make a database connection.
   * @param array $options Associative array of database settings.
   * @param (string|Schema)[] $schemas An array of table/schema-names and
   * schemas to be attached to the database .
   * @param string $name An optional name for database connection, if the name
   * is provided, the connection and the associated tables will be added to 
   * this Databases-object .
   * @throws ConfigurationException If the $options-array does not
   * contain the necessary information for a connection to be made.
   * @throws InvalidSchemaException If one of the schema names listed
   * in the $schemas-parameter is unknown.
   * @throws ConnectionException If the connection fails.
   * @return LoadableDatabase A database object.
   */
  public function connect($options, $schemas, $name = null) {
    if (is_string($options)) {
      $name = $options;
      if (!isset($this->config[$name])) {
        throw new ConfigurationException(
          tr('Database "%1" not configured', $name)
        );
      }
      $options = $this->config->getSubset($name);
    }
    $driver = $options->get('driver', null);
    if (!isset($driver))
      throw new ConfigurationException(tr(
        'Database driver not set'
      ));
    try {
      $driverInfo = $this->drivers->checkDriver($driver);
    }
    catch (InvalidDriverException $e) {
      throw new ConnectionException(tr('Invalid database driver: %1', $e->getMessage()), 0, $e);
    }
    foreach ($driverInfo['requiredOptions'] as $option) {
      if (!isset($options[$option])) {
        throw new ConfigurationException(
          tr('Database option missing: "%1"', $option)
        );
      }
    }
    try {
      $class = 'Jivoo\Databases\Drivers\\' . $driver  . '\\' . $driver . 'Database';
      Utilities::assumeSubclassOf($class, 'Jivoo\Databases\LoadableDatabase');
      $dbSchema = new DatabaseSchema();
      foreach ($schemas as $schema) {
        if (is_string($schema)) {
          $name = $schema;
          $schema = $this->getSchema($name);
          if (!isset($schema)) {
            throw new InvalidSchemaException(
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
    catch (ConnectionException $exception) {
      throw new ConnectionException(
        tr('Database connection failed (%1): %2', $driver, $exception->getMessage()),
        0, $exception
      );
    }
  }
  
  /**
   * Close all connections.
   */
  public function close() {
    foreach ($this->connections as $connection)
      $connection->close();
  }
  
  /**
   * Begin transaction in all connections.
   */
  public function beginTransaction() {
    foreach ($this->connections as $connection)
      $connection->beginTransaction();
  }
  
  /**
   * Commit all transactions.
   */
  public function commit() {
    foreach ($this->connections as $connection)
      $connection->commit();
  }
  
  /**
   * Rollback all transactions.
   */
  public function rollback() {
    foreach ($this->connections as $connection)
      $connection->rollback();
  }
}
