<?php
class Database extends DatabaseDriver implements IModule {
  private $configuration;
  private $routes;
  private $templates;
  private $actions;
  private $errors;
  private $http;

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getRoutes() {
    return $this->routes;
  }

  public function getTemplates() {
    return $this->templates;
  }

  public function getActions() {
    return $this->actions;
  }

  public function getHttp() {
    return $this->http;
  }

  private $driver;
  private $driverInfo;
  private $connection;

  public function __construct(Routes $routes, Actions $actions) {
    $this->routes = $routes;
    $this->actions = $actions;
    $this->templates = $this->routes->getTemplates();
    $this->configuration = $this->templates->getConfiguration();
    $this->errors = $this->configuration->getErrors();
    $this->http = $this->routes->getHttp();

    if (!$this->configuration->exists('database.driver')) {
      $this->routes->setRoute(array($this, 'selectDriverController'), 10);
      $this->routes->callController();
      exit;
    }
    else {
      $this->driver = $this->configuration->get('database.driver');
      $this->driverInfo = $this->checkDriver($this->driver);
      if (!$this->driverInfo OR !$this->driverInfo['isAvailable']) {
        $this->configuration->delete('database.driver');
        $this->http->refreshPath();
      }
      foreach ($this->driverInfo['requiredOptions'] as $option) {
        if (!$this->configuration->exists('database.' . $option)) {
          $this->routes->setRoute(array($this, 'setupDriverController'), 10);
          $this->routes->callController();
          exit;
        }
      }
      try {
        $this->connection = call_user_func(
          array(fileClassName($this->driver), 'connect'),
          $this->configuration->get('database.')
        );
      }
      catch (DatabaseConnectionFailedException $exception) {
        $this->errors->fatal(
          tr('Database connection failed'),
          tr('Could not connect to the database.'),
          '<p>' . $exception->getMessage() . '</p>'
        );
      }
      if ($this->configuration->exists('database.tablePrefix')) {
        $this->tablePrefix = $this->configuration->get('database.tablePrefix');
        $this->connection->tablePrefix = $this->tablePrefix;
      }
      ActiveRecord::connect($this);
    }
  }

  public static function getDependencies() {
    return array('routes', 'actions');
  }

  public function close() {
    if ($this->connection) {
      $this->connection->close();
    }
  }

  public static function connect($options = array()) {

  }

  public function execute(Query $query) {
    return $this->connection->execute($query);
  }

  public function executeSelect(Query $query) {
    return $this->connection->executeSelect($query);
  }

  public function count($table, SelectQuery $query = NULL) {
    return $this->connection->count($table, $query);
  }

  public function tableExists($table) {
    return $this->connection->tableExists($table);
  }

  public function getColumns($table) {
    return $this->connection->getColumns($table);
  }

  public function getPrimaryKey($table) {
    return $this->connection->getPrimaryKey($table);
  }

  public function getIndexes($table) {
    return $this->connection->getIndexes($table);
  }

  public function escapeString($string) {
    return $this->connection->escapeString($string);
  }

  public function checkDriver($driver) {
    if (!file_exists(p(CLASSES . 'db-drivers/' . $driver . '.class.php'))) {
      return FALSE;
    }
    require_once(p(CLASSES . 'db-drivers/' . $driver . '.class.php'));
    $className = fileClassName($driver);
    $reflection = new ReflectionClass($className);
    if (!$reflection->implementsInterface('IDatabase')) {
      continue;
    }
    $dependencies = call_user_func(array($className, 'getDriverDependencies'));
    $missing = array();
    foreach ($dependencies as $dependency) {
      if (!extension_loaded($dependency)) {
        $missing[] = $dependency;
      }
    }
    return array(
      'driver' => $driver,
      'name' => call_user_func(array($className, 'getDriverName')),
      'requiredOptions' => call_user_func(array($className, 'getRequiredOptions')),
      'isAvailable' => count($missing) < 1,
      'link' => $this->http->getLink(NULL, array('select' => $driver)),
      'missingExtensions' => $missing
    );
  }

  public function listDrivers() {
    $drivers = array();
    $dir = opendir(p(CLASSES . 'db-drivers/'));
    while ($file = readdir($dir)) {
      if (substr($file, -10) == '.class.php') {
        $driver = substr($file, 0, -10);
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
      $this->configuration->set('database.driver', $_GET['select']);
      $this->http->refreshPath(array());
    }
    else {
      $this->templates->renderTemplate('backend/select-driver.html', $templateData);
    }
  }

  public function setupDriverController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['driver'] = $this->driverInfo;
    $templateData['cancelAction'] = $this->actions->add('unset-driver');
    $templateData['saveAction'] = $this->actions->add('save');
    $templateData['title'] = tr('Welcome to PeanutCMS');
    if ($this->actions->has('unset-driver')) {
      $this->configuration->delete('database.driver');
      $this->http->refreshPath();
    }
    if ($this->actions->has('save')) {
      $this->configuration->set('database.server', $_POST['server']);
      $this->configuration->set('database.username', $_POST['username']);
      $this->configuration->set('database.password', $_POST['password']);
      $this->configuration->set('database.database', $_POST['database']);
      $this->configuration->set('database.tablePrefix', $_POST['tablePrefix']);
      $this->http->refreshPath();
    }
    $this->templates->renderTemplate('backend/setup-driver.html', $templateData);
  }

  public static function getDriverName() {
    return '';
  }

  public static function getDriverDependencies() {
    return array();
  }

  public static function getRequiredOptions() {
    return array();
  }

}
