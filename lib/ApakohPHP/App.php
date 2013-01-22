<?php
/**
 * Application class for initiating ApakohPHP applications
 * @package ApakohPHP
 */
class App {

  private $appConfig = array();

  private $userConfig = array();

  private $paths = null;

  private $name = 'ApakohPHP Application';

  private $version = '0.0.0';

  private $modules = array(
  );

  private $m = null;

  /* EVENTS BEGIN */
  private $events = null;

  /**
   * Event, triggered each time a module is loaded
   * @param callback $h Attach an event handler
   * @uses ModuleLoadedEventArgs
   */
  public function onModuleLoaded($h) { $this->events->attach($h); }
  /**
   * Event, triggered when all modules are loaded
   * @param callback $h Attach an event handler
   */
  public function onModulesLoaded($h) { $this->events->attach($h); }
  /**
   * Event, triggered when ready to render page
   * @param callback $h Attach an event handler
   */
  public function onRender($h) { $this->events->attach($h); }
  /* EVENTS END */

  /**
   * Create application
   * @param array $appConfig Associative array containing at least the 'path'-key
   */
   public function __construct($appConfig) {
    if (!isset($appConfig['path'])) {
      throw new Exception('Application path not set.');
    }
    $this->events = new Events($this);
    $this->m = new Dictionary();
    $this->paths = new PathDictionary(dirname($_SERVER['SCRIPT_FILENAME']), $appConfig['path']);
    $this->paths->app = $appConfig['path'];
    $this->paths->web = dirname($_SERVER['SCRIPT_NAME']);
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

  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'paths':
      case 'name':
      case 'version':
        return $this->$property;
    }
  }

  /**
   * Set value of property
   * @param string $property Property name
   * @param mixed $value Value
   */
  public function __set($property, $value) {
    switch ($property) {
    }
  }

  /**
   * Request a module
   * @param string $module Module name
   * @return ModuleBase|false Module object or false if module is
   * not loaded
   */
  public function requestModule($module) {
    try {
      return $this->m->$module;
    }
    catch (DictionaryKeyInvalidException $e) {
      return false;
    }
  }

  /**
   * Get the absolute path of a file
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path = '') {
    return $this->paths->$key . '/' . $path;
  }

  /**
   * Get the absolute path of a file relative to the public directory
   * @param string $path File
   * @return string Path
   */
  public function w($path = '') {
    return $this->paths->web . '/' . $path;
  }

  public function loadModule($module) {
    $moduleName = $module;
    if (strpos($module, '.') !== false) {
      $segments = explode('.', $module);
      $moduleName = $segments[count($segments) - 1];
    }
    if (!isset($this->m->$moduleName)) {
      if (!class_exists($moduleName)) {
        if (!Lib::import($module . '.*')) {
          throw new ModuleNotFoundException(tr('The "%1" module could not be found', $module));
        }
        if (!class_exists($moduleName)) {
          throw new ModuleInvalidException(tr('The "%1" module does not have a main class', $module));
        }
      }
      $info = Lib::getModuleInfo($module);
      if (!$info) {
        throw new ModuleInvalidException(tr('The "%1" module is invalid', $module));
      }
      $dependencies = $info['dependencies']['modules'];
      $modules = array();
      foreach ($dependencies as $dependency => $versionInfo) {
        try {
          $dependencyObject = $this->loadModule($dependency);
          $modules[get_class($dependencyObject)] = $dependencyObject;
        }
        catch (ModuleNotFoundException $e) {
          throw new ModuleMissingDependencyException(tr(
            'The "%1" module depends on the "%2" module, which could not be found',
            $module,
            $dependency
          ));
        }
      }
      $this->paths->$moduleName = LIB_PATH . '/' . implode('/', $segments);
      $this->m->$moduleName = new $moduleName($modules, $this);
    }
    return $this->m->$moduleName;
  }

  /**
   * Run the application
   * @param string $environment Configuration environment to use
   */
  public function run($environment = 'production') {
    if ($environment == 'development') {
      define('DEBUG', true);
    }
    define('CFG', $this->paths->config . '/');
    define('WEBPATH', $this->paths->web . '/');
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

    Lib::addIncludePath($this->paths->controllers);
    Lib::addIncludePath($this->paths->views);
    Lib::addIncludePath($this->paths->helpers);
    Lib::addIncludePath($this->paths->models);
    Lib::addIncludePath($this->paths->config . '/schemas');

    foreach ($this->modules as $module) {
      $object = $this->loadModule($module);
      $this->events->trigger('onModuleLoaded', new ModuleLoadedEventArgs($module, $object));
    }
    $this->events->trigger('onModulesLoaded');
    $this->events->trigger('onRender');
  }
}

/**
 * Thrown when a requested module is not loaded
 * @package ApakohCMS
 */
class ModuleNotLoadedException extends Exception { }
/**
 * Thrown when a module does not exist
 * @package ApakohCMS
 */
class ModuleNotFoundException extends Exception { }
/**
 * Thrown when a module is invalid
 * @package ApakohCMS
 */
class ModuleInvalidException extends Exception { }
/**
 * Thrown when a module is missing dependencies
 * @package ApakohCMS
 */
class ModuleMissingDependencyException extends ModuleNotFoundException { }
/**
 * Thrown when a module is blacklisted
 * @package ApakohPHP
 */
class ModuleBlacklistedException extends Exception { }

/**
 * EventArgs to be sent with the onModuleLoaded event
 * @property-read string $module Module name
 * @property-read ModuleBase $object Module object
 * @package ApakohPHP
 */
class ModuleLoadedEventArgs extends EventArgs {
  protected $module;
  protected $object;
}
