<?php
// Module
// Name           : Database
// Version        : 0.2.0
// Description    : The PeanutCMS database system
// Author         : PeanutCMS
// Dependencies   : configuration routes templates actions errors http

class Database extends ModuleBase implements IDatabase  {
  private $driver;
  private $driverInfo;
  private $connection;

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

  public function migrate(IMigration $migration) {
    if ($this->connection) {
      return $this->connection->migrate($migration);
    }
  }
  /* End IDatabase implementation */

  protected function init() {
    if (!$this->m->Configuration->exists('database.driver')) {
      $this->m->Routes->setRoute(array($this, 'selectDriverController'), 10);
      $this->m->Routes->callController();
      exit;
    }
    else {
      $this->driver = $this->m->Configuration->get('database.driver');
      $this->driverInfo = $this->checkDriver($this->driver);
      if (!$this->driverInfo OR !$this->driverInfo['isAvailable']) {
        $this->m->Configuration->delete('database.driver');
        $this->m->Http->refreshPath();
      }
      foreach ($this->driverInfo['requiredOptions'] as $option) {
        if (!$this->m->Configuration->exists('database.' . $option)) {
          $this->m->Routes->setRoute(array($this, 'setupDriverController'), 10);
          $this->m->Routes->callController();
          exit;
        }
      }
      require(p(CLASSES . 'database/' . $this->driver . '.php'));
      try {
        $this->connection = new $this->driver($this->m->Configuration->get('database'));
      }
      catch (DatabaseConnectionFailedException $exception) {
        Errors::fatal(
          tr('Database connection failed'),
          tr('Could not connect to the database.'),
          '<p>' . $exception->getMessage() . '</p>'
        );
      }
    }
  }

  public function checkDriver($driver) {
    $driver = className($driver);
    if (!file_exists(p(CLASSES . 'database/' . $driver . '.php'))) {
      return FALSE;
    }
    $meta = readFileMeta(p(CLASSES . 'database/' . $driver . '.php'));
    $missing = array();
    foreach ($meta['dependencies']['php'] as $dependency => $versionInfo) {
      if (!extension_loaded($dependency)) {
        $missing[] = $dependency;
      }
    }
    return array(
      'driver' => $driver,
      'name' => $meta['name'],
      'requiredOptions' => $meta['required'],
      'isAvailable' => count($missing) < 1,
      'link' => $this->m->Http->getLink(NULL, array('select' => $driver)),
      'missingExtensions' => $missing
    );
  }

  public function listDrivers() {
    $drivers = array();
    $dir = opendir(p(CLASSES . 'database/'));
    while ($file = readdir($dir)) {
      if (substr($file, -4) == '.php') {
        $driver = substr($file, 0, -4);
        if ($driverInfo = $this->checkDriver($driver)) {
          $drivers[$driver] = $driverInfo;
        }
      }
    }
    return $drivers;
  }

  public function selectDriverController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['drivers'] = $this->listDrivers();
    $templateData['backendMenu'] = FALSE;
    $templateData['title'] = tr('Welcome to PeanutCMS');
    if (isset($_GET['select']) AND isset($templateData['drivers'][$_GET['select']])) {
      $this->m->Configuration->set('database.driver', $_GET['select']);
      $this->m->Http->refreshPath(array());
    }
    else {
      $this->m->Templates->renderTemplate('backend/select-driver.html', $templateData);
    }
  }

  public function setupDriverController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['driver'] = $this->driverInfo;
    $templateData['cancelAction'] = $this->actions->add('unset-driver');
    $templateData['saveAction'] = $this->actions->add('save');
    $templateData['title'] = tr('Welcome to PeanutCMS');
    if ($this->m->Actions->has('unset-driver')) {
      $this->m->Configuration->delete('database.driver');
      $this->m->Http->refreshPath();
    }
    if ($this->m->Actions->has('save')) {
      $this->m->Configuration->set('database.server', $_POST['server']);
      $this->m->Configuration->set('database.username', $_POST['username']);
      $this->m->Configuration->set('database.password', $_POST['password']);
      $this->m->Configuration->set('database.database', $_POST['database']);
      $this->m->Configuration->set('database.tablePrefix', $_POST['tablePrefix']);
      $this->m->Http->refreshPath();
    }
    $this->m->Templates->renderTemplate('backend/setup-driver.html', $templateData);
  }

}
