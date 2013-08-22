<?php
/**
 * Application class for initiating ApakohPHP applications
 * @package Core
 */
class App {

  private $appConfig = array();

  private $config = array();

  private $paths = null;

  private $basePath = '/';

  private $name = 'ApakohPHP Application';

  private $version = '0.0.0';

  private $minPhpVersion = '5.2.0';

  private $modules = array('Core');

  private $m = null;

  private $environment = 'production';

  private $sessionPrefix = 'apakoh_';

  /* EVENTS BEGIN */
  private $events = null;

  /**
   * Event, triggered each time a module is loaded
   * @param callback $h Attach an event handler
   * @uses ModuleLoadedEventArgs
   */
  public function onModuleLoaded($h) {
    $this->events->attach($h);
  }
  /**
   * Event, triggered when all modules are loaded
   * @param callback $h Attach an event handler
   */
  public function onModulesLoaded($h) {
    $this->events->attach($h);
  }
  /**
   * Event, triggered when ready to render page
   * @param callback $h Attach an event handler
   */
  public function onRender($h) {
    $this->events->attach($h);
  }
  /* EVENTS END */

  /**
   * Create application
   * @param array $appConfig Associative array containing at least the 'path'-key
   */
  public function __construct($appConfig) {
    if (!isset($appConfig['path'])) {
      throw new Exception('Application path not set.');
    }
    $this->appConfig = $appConfig;
    $this->events = new Events($this);
    $this->m = new Dictionary();
    $this->paths = new PathDictionary(
      dirname($_SERVER['SCRIPT_FILENAME']),
      $appConfig['path']
    );
    $this->paths->app = $appConfig['path'];
    $this->paths->lib = LIB_PATH;
    $this->paths->core = CORE_LIB_PATH;
    $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
    if (isset($appConfig['name'])) {
      $this->name = $appConfig['name'];
    }
    if (isset($appConfig['version'])) {
      $this->version = $appConfig['version'];
    }
    if (isset($appConfig['minPhpVersion'])) {
      $this->minPhpVersion = $appConfig['minPhpVersion'];
    }
    if (isset($appConfig['modules'])) {
      $this->modules = $appConfig['modules'];
    }
    if (!isset($appConfig['defaultLanguage'])) {
      $this->appConfig['defaultLanguage'] = 'en';
    }
    if (isset($appConfig['sessionPrefix'])) {
      $this->sessionPrefix = $appConfig['sessionPrefix'];
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
      case 'minPhpVersion':
      case 'environment':
      case 'config':
      case 'appConfig':
      case 'sessionPrefix':
      case 'basePath':
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
      case 'basePath':
        $this->$property = $value;
    }
  }

  /**
   * Request a module
   * @param string $module Module name
   * @return ModuleBase|false Module object or false if module is
   * not loaded
   */
  public function requestModule($module) {
    $moduleName = $module;
    if (strpos($module, '/') !== false) {
      $segments = explode('/', $module);
      $moduleName = $segments[count($segments) - 1];
    }
    try {
      return $this->m->$moduleName;
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
    if ($this->basePath == '/') {
      return '/' . $path;
    }
    return $this->basePath . '/' . $path;
  }

  public function loadModule($module) {
    $segments = explode('/', $module);
    $moduleName = $segments[count($segments) - 1];
    if (!isset($this->m->$moduleName)) {
      if (!Lib::classExists($moduleName, false)) {
        if (!Lib::import($module)) {
          throw new ModuleNotFoundException(
            tr('The "%1" module could not be found', $module)
          );
        }
        if (!Lib::classExists($moduleName)) {
          throw new ModuleInvalidException(
            tr('The "%1" module does not have a main class', $module)
          );
        }
      }
      $info = Lib::getModuleInfo($module);
      if (!$info) {
        throw new ModuleInvalidException(
          tr('The "%1" module is invalid', $module)
        );
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
            $module, $dependency
          ));
        }
      }
      $this->paths->$moduleName = LIB_PATH . '/' . implode('/', $segments);
      $this->m->$moduleName = new $moduleName($modules, $this);
    }
    return $this->m->$moduleName;
  }
  
  public function handleError(Exception $exception) {
    /** @todo attempt to create error report */
    $hash = substr(md5($exception->__toString()), 0, 10);
    if (!file_exists($this->p('log', 'crash-' . $hash . '.log'))) {
      Logger::attachFile(
        $this->p('log', 'crash-' . $hash . '.log'),
        Logger::ALL
      );
    }
    if ($this->config['core']['showExceptions']) {
      $app = $this->name;
      $version = $this->version;
      $title = tr('Uncaught exception');
      include CORE_LIB_PATH . '/ui/layout.php';
      $this->stop();
    }
    else {
      /** @todo allow custom error page */
      include CORE_LIB_PATH . '/ui/error.php';
      $this->stop();
    }
  }

  /**
   * Run the application
   * @param string $environment Configuration environment to use
   */
  public function run($environment = 'production') {
    $this->environment = $environment;

    if (version_compare(phpversion(), $this->minPhpVersion) < 0) {
      echo 'Sorry, but ' . $this->name
        . ' does not support PHP versions below ';
      echo $this->minPhpVersion . '. ';
      echo 'You are currently using version ' . phpversion() . '. ';
      echo 'You should update PHP or contact your hosting provider. ';
      return;
    }

    $this->config = new AppConfig($this->p('config', 'config.php'));
    $this->config->setVirtual('app', $this->appConfig);

    $environmentConfigFile = $this
      ->p('config', 'environments/' . $environment . '.php');
    if (file_exists($environmentConfigFile)) {
      $this->config->override = include $environmentConfigFile;
    }
    else {
      Logger::notice(
        'Configuration file for environment "' . $environment . '" not found'
      );
    }
    
    $this->config->defaults = array(
      'core' => array(
        'language' => $this->appConfig['defaultLanguage'],
        'timeZone' => @date_default_timezone_get(), /** @todo Reevaluate use of @ */
        'showExceptions' => false,
        'logLevel' => Logger::ALL,
      ),
    );

    Logger::attachFile(
      $this->p('log', $this->environment . '.log'),
      $this->config['core']['logLevel']
    );

    // I18n system
    I18n::setup($this->config['core'], $this->paths->languages);

    // Error handling
    ErrorReporting::setHandler(array($this, 'handleError'));

    foreach ($this->modules as $module) {
      $object = $this->loadModule($module);
      $this->events->trigger(
        'onModuleLoaded',
        new ModuleLoadedEventArgs($module, $object)
      );
    }
    $this->events->trigger('onModulesLoaded');
    $this->events->trigger('onRender');
  }
  
  public function stop($status = 0) {
    exit($status);
  }
}

/**
 * Thrown when a requested module is not loaded
 * @package Core
 */
class ModuleNotLoadedException extends Exception {
}
/**
 * Thrown when a module does not exist
 * @package Core
 */
class ModuleNotFoundException extends Exception {
}
/**
 * Thrown when a module is invalid
 * @package Core
 */
class ModuleInvalidException extends Exception {
}
/**
 * Thrown when a module is missing dependencies
 * @package Core
 */
class ModuleMissingDependencyException extends ModuleNotFoundException {
}
/**
 * Thrown when a module is blacklisted
 * @package Core
 */
class ModuleBlacklistedException extends Exception {
}

/**
 * EventArgs to be sent with the onModuleLoaded event
 * @property-read string $module Module name
 * @property-read ModuleBase $object Module object
 * @package Core
 */
class ModuleLoadedEventArgs extends EventArgs {
  protected $module;
  protected $object;
}
