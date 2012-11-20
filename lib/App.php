<?php

class App {

  private $appConfig = array();

  private $userConfig = array();

  private $path;

  private $configPath;

  private $name = 'ArachisPHP Application';

  private $version = '0.0.0';

  private $modules = array(
    'Errors', 'Configuration', 'Shadow', 'I18n', 'Http', 'Templates',
    'Routes', 'Database'
  );


  public function __construct($appConfig) {
    if (!isset($appConfig['path'])) {
      throw new Exception('Application path not set.');
    }
    $this->path = rtrim($appConfig['path'], '/\\');
    $this->configPath = $this->path . '/config';
    if (isset($appConfig['name'])) {
      $this->name = $appConfig['name'];
    }
    if (isset($appConfig['version'])) {
      $this->version = $appConfig['version'];
    }
    if (isset($appConfig['modules'])) {
      $this->modules = $appConfig['modules'];
    }
  }

  public function __get($property) {
    switch ($property) {
      case 'appConfig':
      case 'path':
      case 'name':
      case 'version':
        return $this->$property;
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'configPath':
        $this->$property = $value;
    }
  }

  public function run($environment = 'production') {
    define('CFG', $this->configPath . '/');
    if (!require_once(LIB_PATH . '/essentials.php')) {
      echo 'Essential PeanutCMS files are missing. You should probably reinstall.';
      return;
    }

    // The autoloader has to be registered BEFORE session_start()
    session_start();

    if (PHP_VERSION_ID < 50200) {
      echo 'Sorry, but PeanutCMS does not support PHP versions below 5.2.0. ';
      echo 'You are currently using version ' . PHP_VERSION .'. ';
      echo 'You should contact your webhost. ';
      return;
    }

    Lib::addIncludePath($this->path . '/controllers');
    Lib::addIncludePath($this->path . '/models');
    Lib::addIncludePath($this->path . '/helpers');
    Lib::addIncludePath($this->configPath . '/schemas');
    Core::main($this->modules);
  }
}
