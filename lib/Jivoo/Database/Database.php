<?php
// Module
// Name           : Database
// Description    : The Jivoo database system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Routing Jivoo/Templates Jivoo/Models Jivoo/Helpers
//                  Jivoo/Controllers Jivoo/Setup

Lib::import('Jivoo/Database/Mixins');

/**
 * Database module
 * @package Jivoo\Database
 */
class Database extends LoadableModule implements IDatabase {
  
  protected $modules = array('Routing', 'Templates', 'Models', 'Helpers', 'Controllers', 'Setup');
  
  /**
   * @var string Driver name
   */
  private $driver;
  
  /**
   * @var array Associative array of driver info
   */
  private $driverInfo;

  /**
   * @var IDatabase Database connection
   */
  private $connection;
  
  /**
   * @var IModel[]
   */
  private $tables = array();

  /**
   * 
   * @var array Associative array of table names and schemas
   */
  private $schemas = array();
  
  /**
   * @var array Associative array of table names and migration status
   */
  private $migrations = array();

  /* Begin IDatabase implementation */
  public function __get($table) {
    if (isset($this->tables[$table]))
      return $this->tables[$table];
    throw new TableNotFoundException(tr('Table "%1" not found', $table));
  }

  public function __isset($table) {
    if (isset($this->tables[$table]))
      return true;
    if ($this->connection)
      return $this->connection->__isset($table);
    return false;
  }

  public function close() {
    if ($this->connection)
      $this->connection->close();
  }

  public function getTable($table, ISchema $schema) {
    if (!isset($this->tables[$table]))
      $this->tables[$table] = $this->connection->getTable($table, $schema);
    return $this->tables[$table];
  }

  public function tableExists($table) {
    return isset($this->tables[$table]);
  }

  public function migrate(Schema $schema, $force = false) {
    if ($schema->getName() == 'undefined') {
      return false;
    }
    $name = $schema->getName();
    if (!$force AND isset($this->config['migration'][$name])) {
      if ($this->config['migration'][$name] == $this->app->version) {
        return 'unchanged';
      }
    }
    if ($this->connection) {
      $status = $this->connection->migrate($schema);
      $this->config['migration'][$name] = $this->app->version;
      if ($status == 'new')
        $this->config['installed'][$name] = false;
      return $status;
    }
  }
  /* End IDatabase implementation */

  protected function init() {
    $this->config->defaults = array('server' => 'localhost',
      'database' => strtolower($this->app->name),
      'filename' => $this->p('config', 'db.sqlite3'),
    );
    $controller = new DatabaseSetupController($this->m->Routing,
      $this->m->Templates, $this->config);
    $controller->addModule($this);
    $this->m->Helpers->addHelpers($controller);
    $this->view->addTemplateDir($this->p('templates'), 3);
    if (!isset($this->config['driver'])) {
      $this->m->Setup->enterSetup($controller, 'selectDriver');
    }
    else {
      Logger::debug('Check driver: ' . $this->config['driver']);
      $this->driver = $this->config['driver'];
      $this->driverInfo = $this->checkDriver($this->driver);
      if (!$this->driverInfo OR !$this->driverInfo['isAvailable']) {
        unset($this->config['driver']);
        $this->m->Routing->refresh();
      }
      if ($this->config['configured'] !== true) {
        $controller->driver = $this->driverInfo;
        $this->m->Setup->enterSetup($controller, 'setupDriver');
      }
      foreach ($this->driverInfo['requiredOptions'] as $option) {
        if (!isset($this->config[$option])) {
          $controller->driver = $this->driverInfo;
          $this->m->Setup->enterSetup($controller, 'setupDriver');
        }
      }
      Lib::import('Jivoo/Database/' . $this->driver);
      try {
        $class = $this->driver . 'Database';
        if (!Lib::classExists($class))
          throw new InvalidDatabaseDriverException(tr(
            'Database driver "%1" could not be loaded because the class "%2" does not exist',
            $this->driver, $class
          ));
        if (!is_subclass_of($class, 'MigratableDatabase'))
          throw new InvalidDatabaseDriverException(tr(
            'Class "%1" must extend class "%2"',
            $class, 'MigratableDatabase'
          ));
        $this->connection = new $class($this->app, $this->config);
      }
      catch (DatabaseConnectionFailedException $exception) {
        /** @todo Do something ... here */
        throw new Exception(
          tr('Database connection failed')
            . tr('Could not connect to the database.') . '<p>'
            . $exception->getMessage() . '</p>');
      }
    }

    $schemasDir = $this->p('schemas', '');
    if (is_dir($schemasDir)) {
      Lib::addIncludePath($schemasDir);
      $dir = opendir($schemasDir);
      if ($dir) {
        while ($file = readdir($dir)) {
          $split = explode('.', $file);
          if (isset($split[1]) AND $split[1] == 'php') {
            $class = $split[0];
            $this->addSchema(new $class());
          }
        }
      }
    }
    closedir($dir);

    $classes = $this->m->Models->getModelClasses();
    foreach ($classes as $class) {
      $this->addActiveModel($class);
    }
    
    $this->m->Routing->attachEventHandler('beforeRender', array($this, 'installModels'));
  }
  
  public function installModels($caller = null, $eventArgs = null) {
    foreach ($this->tables as $name => $table) {
      if ($table instanceof ActiveModel
          and !(isset($this->config['installed'][$name])
            and $this->config['installed'][$name])) {
        $table->install();
        $this->config['installed'][$name] = true;
      } 
    }
  }
  
  /**
   * Add a Schema if it has not already been added
   * @param string $name Schema name
   * @param string $file Path to schema file
   * @return boolean True if missing and added, false otherwise
   */
  public function addSchemaIfMissing($name, $file) {
    if ($this->hasSchema($name)) {
      return false;
    }
    include $file;
    $class = $name . 'Schema';
    $this->addSchema(new $class());
    return true;
  }
  
  /**
   * Add an active model if it has not already been added
   * @param string $class Class name of active model
   * @param string $file Path to model class file
   * @return True if missing and added successfully, false otherwise
   */
  public function addActiveModelIfMissing($name, $file) {
    if (isset($this->m->Models->$name)) {
      return false;
    }
    if (!Lib::classExists($name, false)) {
      include $file;
    }
    return $this->addActiveModel($name);
  }
  
  /**
   * Whether or not a schema has been added
   * @param string $name Schema (table) name
   * @return True if schema exists, false otherwise
   */
  public function hasSchema($name) {
    return isset($this->schemas[$name]);
  }
  
  /**
   * Add Schema and run migration if necessary
   * @param Schema $schema Schema
   * @return True if added
   */
  public function addSchema(Schema $schema) {
    $name = $schema->getName();
    $this->schemas[$name] = $schema;
    $this->migrations[$name] = $this->migrate($this->schemas[$name]);
    if (!isset($this->$name) AND $this->migrations[$name] == 'unchanged') {
      $this->migrations[$name] = $this->migrate($this->schemas[$name], true);
    }
    $this->tables[$name] = $this->connection->getTable($name, $this->schemas[$name]);
    return true;
  }
  
  /**
   * Add an active model
   * @param string $class Class name of active model
   * @return True if successfull, false if table not found
   */
  public function addActiveModel($class) {
    if (is_subclass_of($class, 'ActiveModel')) {
      $model = new $class($this->app, $this);
      $this->m->Models->setModel($class, $model);
      $this->tables[$class] = $model;
      return true;
    }
    return false;
  }

  /**
   * Check if a table is newly created
   * @param string $table Table name
   * @return boolean True if new, false otherwise
   */
  public function isNew($table) {
    return isset($this->migrations[$table])
      AND $this->migrations[$table] == 'new';
  }

  /**
   * Get information about a database driver.
   * 
   * The returned information array is of the format:
   * <code>
   * array(
   *   'driver' => ..., // Driver name (string)
   *   'name' => ..., // Formal name, e.g. 'MySQL' instead of 'MySql' (string)
   *   'requiredOptions' => array(...), // List of required options (string[])
   *   'optionalOptions' => array(...), // List of optional options (string[])
   *   'isAvailable' => ..., // Whether or not driver is available (bool)
   *   'missingExtensions => array(...) // List of missing extensions (string[])
   * )
   * </code>
   * @param string $driver Driver name
   * @return array|false Driver information as an associative array or false if
   * not found
   */
  public function checkDriver($driver) {
    if (!file_exists($this->p($driver . '/' . $driver . 'Database.php'))) {
      return false;
    }
    $meta = FileMeta::read($this->p($driver . '/' . $driver . 'Database.php'));
    if (!isset($meta['required'])) {
      $meta['required'] = '';
    }
    $missing = array();
    foreach ($meta['dependencies']['php'] as $dependency => $versionInfo) {
      if (!extension_loaded($dependency)) {
        $missing[] = $dependency;
      }
    }
    return array('driver' => $driver, 'name' => $meta['name'],
      'requiredOptions' => explode(' ', $meta['required']),
      'optionalOptions' => explode(' ', $meta['optional']),
      'isAvailable' => count($missing) < 1, 'missingExtensions' => $missing
    );
  }

  /**
   * Get an array of all drivers and their information 
   * @return array An associative array of driver names and driver information
   * as returned by {@see Database::checkDriver()}
   */
  public function listDrivers() {
    $drivers = array();
    $dir = opendir($this->p(''));
    while ($driver = readdir($dir)) {
      if (is_dir($this->p($driver))) {
        if ($driverInfo = $this->checkDriver($driver)) {
          $drivers[$driver] = $driverInfo;
        }
      }
    }
    return $drivers;
  }

}

class InvalidDatabaseDriverException extends Exception { }
