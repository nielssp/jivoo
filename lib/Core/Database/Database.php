<?php
// Module
// Name           : Database
// Version        : 0.2.0
// Description    : The Apakoh Core database system
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates Core/Models Core/Helpers
//                  Core/Controllers Core/Setup

/**
 * Database module
 * 
 * @package Core
 * @subpackage Database
 */
class Database extends ModuleBase implements IDatabase {
  private $driver;
  private $driverInfo;
  private $connection;

  private $schemas = array();
  private $migrations = array();

  /* Begin IDatabase implementation */
  public function __get($table) {
    if ($this->connection) {
      return $this->connection->__get($table);
    }
  }

  public function __isset($table) {
    if ($this->connection) {
      return $this->connection->__isset($table);
    }
  }

  public function close() {
    if ($this->connection) {
      $this->connection->close();
    }
  }

  public function getTable($table) {
    if ($this->connection) {
      return $this->connection->getTable($table);
    }
  }

  public function tableExists($table) {
    if ($this->connection) {
      return $this->connection->tableExists($table);
    }
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
    $controller->addTemplatePath($this->p('templates'));
    if (!isset($this->config['driver'])) {
      $this->m->Setup->enterSetup($controller, 'selectDriver');
    }
    else {
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
      Lib::import('Core/Database/' . $this->driver);
      try {
        $class = $this->driver . 'Database';
        $this->connection = new $class($this->config);
      }
      catch (DatabaseConnectionFailedException $exception) {
        /** @todo Do something ... here */
        throw new Exception(
          tr('Database connection failed')
            . tr('Could not connect to the database.') . '<p>'
            . $exception->getMessage() . '</p>');
      }
    }

    $sources = new Dictionary();

    Lib::addIncludePath($this->p('schemas', ''));
    $dir = opendir($this->p('schemas', ''));
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
        $class = $split[0];
        $name = str_replace('Schema', '', $class);
        $this->schemas[$name] = new $class();
        $this->migrations[$name] = $this->migrate($this->schemas[$name]);
        if (!isset($this->$name) AND $this->migrations[$name] == 'unchanged') {
          $this->migrations[$name] = $this->migrate($this->schemas[$name], true);
        }
        $this->$name->setSchema($this->schemas[$name]);
        $sources->$name = $this->$name;
      }
    }
    closedir($dir);

    $classes = $this->m->Models->getRecordClasses();
    foreach ($classes as $class) {
      if (is_subclass_of($class, 'ActiveRecord')) {
        $table = Utilities::getPlural(strtolower($class));
        if (!isset($this->$table)) {
          $table2 = ActiveModel::getTable($class);
          if (isset($table2)) {
            if (!isset($this->$table2)) {
              throw new Exception(tr('Table not found: "%1"', $table2));
            }
            $table = $table2;
          }
          else {
            throw new Exception(tr('Table not found: "%1"', $table));
          }
        }
        $model = new ActiveModel($class, $this->$table, $this->m->Models,
          $sources);
        $this->m->Models->setModel($class, $model);
      }
    }
  }

  public function isNew($table) {
    return isset($this->migrations[$table])
      AND $this->migrations[$table] == 'new';
  }

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
