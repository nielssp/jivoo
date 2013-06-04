<?php
// Module
// Name           : Database
// Version        : 0.2.0
// Description    : The Apakoh Core database system
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates Core/Models
//                  Core/Maintenance Core/Controllers

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

  /* Begin IDatabase implementation */
  public function __get($table) {
    if ($this->connection) {
      return $this->connection
        ->__get($table);
    }
  }

  public function __isset($table) {
    if ($this->connection) {
      return $this->connection
        ->__isset($table);
    }
  }

  public function close() {
    if ($this->connection) {
      $this->connection
        ->close();
    }
  }

  public function getTable($table) {
    if ($this->connection) {
      return $this->connection
        ->getTable($table);
    }
  }

  public function tableExists($table) {
    if ($this->connection) {
      return $this->connection
        ->tableExists($table);
    }
  }

  public function migrate(Schema $schema) {
    if ($schema->getName() == 'undefined') {
      return false;
    }
    $name = $schema->getName();
    if (isset($this->config['migration'][$name])) {
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
    $this->config->defaults = array(
      'server' => 'localhost',
      'database' => $this->app->name,
      'filename' => $this->p('config', 'db.sqlite3'),
    );
    Lib::addIncludePath($this->p('config', 'schemas'));
    $controller = $this->m->Controllers->DatabaseMaintenance;
    $controller->setConfig($this->config);
    $controller->addModule($this);
    if (!isset($this->config['driver'])) {
      $this->m->Maintenance->setup($controller, 'selectDriver');
    }
    else {
      $this->driver = $this->config['driver'];
      $this->driverInfo = $this->checkDriver($this->driver);
      if (!$this->driverInfo OR !$this->driverInfo['isAvailable']) {
        unset($this->config['driver']);
        $this->m->Routing->refresh();
      }
      if ($this->config['configured'] != true) {
        $this->m->Maintenance
          ->setup($controller, 'setupDriver', array($this->driverInfo));
      }
      foreach ($this->driverInfo['requiredOptions'] as $option) {
        if (!isset($this->config[$option])) {
          $this->m->Maintenance
            ->setup($controller, 'setupDriver', array($this->driverInfo));
        }
      }
      Lib::import('Core/Database/' . $this->driver);
      try {
        $class = $this->driver . 'Database';
        $this->connection = new $class($this->config);
      }
      catch (DatabaseConnectionFailedException $exception) {
        /** @todo Do something ... here */
        throw new Exception(tr('Database connection failed') .
          tr('Could not connect to the database.') .
          '<p>' . $exception->getMessage() . '</p>');
      }
    }
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
