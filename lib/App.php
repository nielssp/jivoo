<?php

class App {

  private $path;

  public function __construct($path) {
    $this->path = rtrim($path, '/\\');
  }

  public function run() {
    if (!require_once(LIB_PATH . '/essentials.php')) {
      echo 'Essential PeanutCMS files are missing. You should probably reinstall.';
      exit;
    }

    // The autoloader has to be registered BEFORE session_start()
    session_start();

    if (PHP_VERSION_ID < 50200) {
      echo 'Sorry, but PeanutCMS does not support PHP versions below 5.2.0. ';
      echo 'You are currently using version ' . PHP_VERSION .'. ';
      echo 'You should contact your webhost. ';
      exit;
    }

    $modules = array(
      'Errors', 'Configuration', 'Shadow', 'I18n', 'Http', 'Templates',
      'Routes', 'Theme', 'Database', 'Authentication', 'Backend',
      'Extensions', 'Posts', 'Links', 'Pages'
    );

    Lib::addIncludePath($this->path . '/controllers');
    Lib::addIncludePath($this->path . '/models');
    Lib::addIncludePath($this->path . '/helpers');
    Lib::addIncludePath($this->path . '/schemas');
    Core::main($modules);
  }
}
