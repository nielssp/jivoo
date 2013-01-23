<?php
// Module
// Name           : Database
// Version        : 0.2.0
// Description    : The PeanutCMS database system
// Author         : PeanutCMS
// Dependencies   : Configuration Routes Templates Errors Http Maintenance

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
    if ($this->m
      ->Configuration
      ->exists('database.migration.' . $name)) {
      if ($this->m
        ->Configuration
        ->get('database.migration.' . $name) == $this->app
            ->version) {
        return 'unchanged';
      }
    }
    if ($this->connection) {
      $status = $this->connection
        ->migrate($schema);
      $this->m
        ->Configuration
        ->set('database.migration.' . $name, $this->app
          ->version);
      return $status;
    }
  }
  /* End IDatabase implementation */

  protected function init() {
    $this->m
      ->Configuration
      ->setDefault(
        array('database.server' => 'localhost',
          'database.database' => 'peanutcms',
          'database.filename' => 'config/db.sqlite3',
        ));
    $controller = new DatabaseMaintenanceController($this->m
        ->Routes, $this->m
        ->Configuration['database']);
    $controller->addModule($this);
    if (!$this->m
      ->Configuration
      ->exists('database.driver')) {
      $this->m
        ->Maintenance
        ->setup($controller, 'selectDriver');
    }
    else {
      $this->driver = $this->m
        ->Configuration
        ->get('database.driver');
      $this->driverInfo = $this->checkDriver($this->driver);
      if (!$this->driverInfo OR !$this->driverInfo['isAvailable']) {
        $this->m
          ->Configuration
          ->delete('database.driver');
        $this->m
          ->Routes
          ->refresh();
      }
      if ($this->m
        ->Configuration
        ->get('database.configured') != 'yes') {
        $this->m
          ->Maintenance
          ->setup($controller, 'setupDriver', array($this->driverInfo));
      }
      foreach ($this->driverInfo['requiredOptions'] as $option) {
        if (!$this->m
          ->Configuration
          ->exists('database.' . $option)) {
          $this->m
            ->Maintenance
            ->setup($controller, 'setupDriver', array($this->driverInfo));
        }
      }
      Lib::import('ApakohPHP/Database/' . $this->driver);
      try {
        $class = $this->driver . 'Database';
        $this->connection = new $class($this->m
          ->Configuration
          ->get('database'));
      }
      catch (DatabaseConnectionFailedException $exception) {
        Errors::fatal(tr('Database connection failed'),
          tr('Could not connect to the database.'),
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
