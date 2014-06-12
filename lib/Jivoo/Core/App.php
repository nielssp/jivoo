<?php
/**
 * Application class for initiating Jivoo applications
 * @package Jivoo\Core
 * @property string $basePath Web base path
 * @property-read PathDictionary $paths Paths
 * @property-read string $name Application name
 * @property-read string $version Application version
 * @property-read string $minPhpVersion Minimum PHP version
 * @property-read string $environment Environment name
 * @property-read AppConfig $config User configuration
 * @property-read array $appConfig Application configuration
 * @property-read string $sessionPrefix Application session prefix
 */
class App implements IEventSubject {
  /**
   * @var array Application configuration
   */
  private $appConfig = array();

  /**
   * @var AppConfig User configuration
   */
  private $config = null;
  
  private $defaultConfig = array();

  /**
   * @var PathMap Paths
   */
  private $paths = null;

  /**
   * @var string Web base path
   */
  private $basePath = '/';
  
  /**
   * @var string Entry script name
   */
  private $entryScript = 'index.php';

  /**
   * @var string Application name
   */
  private $name = 'Jivoo Application';

  /**
   * @var string Application version
   */
  private $version = '0.0.0';

  /**
   * @var string Minimum PHP version
   */
  private $minPhpVersion = '5.2.0';

  /**
   * @var string[] List of modules to load
   */
  private $modules = array('Jivoo/Core');

  /**
   * @var string[] List of modules to import and load
   */
  private $import = array('Jivoo/Core');
  
  private $listenerNames = array();
  
  private $listeners = array();

  /**
   * @var Map Map of modules
   */
  private $m = null;

  /**
   * @var string Environment name
   */
  private $environment = 'production';

  /**
   * @var string Application session prefix
   */
  private $sessionPrefix = 'jivoo_';
  
  private $events = array(
    'beforeImportModules', 'afterImportModules', 'beforeLoadModules',
    'beforeLoadModule', 'afterLoadModule', 'afterLoadModules', 'afterInit'
  );
  
  private $e = null;

  /**
   * Create application
   * @param array $appConfig Associative array containing at least the 'path'-key
   * @param string $entryScript Name of entry script, e.g. 'index.php'
   * @throws Exception if $appconfig['path'] is not set
   */
  public function __construct($appConfig, $entryScript = 'index.php') {
    if (!isset($appConfig['path'])) {
      throw new Exception('Application path not set.');
    }
    $this->appConfig = $appConfig;
    $this->e = new EventManager($this);
    $this->m = new Map();
    $this->paths = new PathMap(
      dirname($_SERVER['SCRIPT_FILENAME']),
      $appConfig['path']
    );
    $this->paths->app = $appConfig['path'];
//     $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
    $this->entryScript = $entryScript;
    
    // Temporary work-around for weird SCRIPT_NAME.
    // When url contains a trailing dot such as
    // /app/index.php/admin./something
    // SCRIPT_NAME returns /PeanutCMS/index.php/admin./something instead of expected
    // /app/index.php
    $script = explode('/', $_SERVER['SCRIPT_NAME']);
    while ($script[count($script) - 1] != $entryScript) {
      array_pop($script);
    }
    $this->basePath = dirname(implode('/', $script));
    // END work-around
    
    if (isset($appConfig['name']))
      $this->name = $appConfig['name'];
    if (isset($appConfig['version']))
      $this->version = $appConfig['version'];
    if (isset($appConfig['minPhpVersion']))
      $this->minPhpVersion = $appConfig['minPhpVersion'];
    if (isset($appConfig['modules']))
      $this->modules = $appConfig['modules'];
    if (isset($appConfig['import']))
      $this->import = $appConfig['import'];
    if (!isset($appConfig['defaultLanguage']))
      $this->appConfig['defaultLanguage'] = 'en';
    if (isset($appConfig['sessionPrefix']))
      $this->sessionPrefix = $appConfig['sessionPrefix'];
    if (isset($appConfig['listeners']))
      $this->listenerNames = $appConfig['listeners'];
    if (isset($appConfig['defaultConfig']))
      $this->defaultConfig = $appConfig['defaultConfig'];

    $this->config = new AppConfig();
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
      case 'entryScript':
        return $this->$property;
      case 'eventManager':
        return $this->e;
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
  
  public function getEvents() {
    return $this->events;
  }

  public function attachEventHandler($name, $callback) {
    $this->e->attachHandler($name, $callback);
  }
  
  public function attachEventListener(IEventListener $listener) {
    $this->e->attachListener($listener);
  }
  
  public function detachEventHandler($name, $callback) {
    $this->e->detachHandler($name, $callback);
  }
  
  public function detachEventListener(IEventListener $listener) {
    $this->e->detachListener($listener);
  }
  
  public function hasEvent($name) {
    return in_array($name, $this->events);
  }
  
  private function triggerEvent($name, Event $event = null) {
    $this->e->trigger($name, $event);
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
  
  public function getModule($name) {
    if (!isset($this->m->$name)) {
      $this->triggerEvent('beforeLoadModule', new LoadModuleEvent($this, $name));
      Lib::assumeSubclassOf($name, 'LoadableModule');
      $this->m->$name = new $name($this);
      $this->triggerEvent('afterLoadModule', new LoadModuleEvent($this, $name, $this->m->$name));
    }
    return $this->m->$name;
  }
  
  public function getModules($modules) {
    foreach ($modules as $name) {
      $this->getModule($name);
    }
    return $this->m;
  }
  
  public function hasModule($name) {
    return isset($this->m->$name);
  }

  
  /**
   * Handler for uncaught exceptions
   * @param Exception $exception The exception
   */
  public function handleError(Exception $exception) {
    /** @todo attempt to create error report */
    if ($this->config['core']['createCrashReports']) {
      $hash = substr(md5($exception->__toString()), 0, 10);
      if (!file_exists($this->p('log', 'crash-' . $hash . '.log'))) {
        Logger::attachFile(
          $this->p('log', 'crash-' . $hash . '.log'),
          Logger::ALL
        );
      }
    }
    // Clean the view
    while (ob_get_level() > 0)
      ob_end_clean(); 
    if ($this->config['core']['showExceptions']) {
      $app = $this->name;
      $version = $this->version;
      $title = tr('Uncaught exception');
      $custom = null;
      try {
        $custom = $this->p('templates', 'error/exception.php');
        if (!file_exists($custom))
          $custom = null;
        else
          include $custom;
      }
      catch (Exception $e) { }
      if (!isset($custom))
        include CORE_LIB_PATH . '/templates/error/exception.php';
      $this->stop();
    }
    else {
      $custom = null;
      try {
        $custom = $this->p('templates', 'error/error.php');
        if (!file_exists($custom))
          $custom = null;
        else
          include $custom;
      }
      catch (Exception $e) { }
      if (!isset($custom))
        include CORE_LIB_PATH . '/templates/error/error.php';
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
    
    Lib::addIncludePath($this->p('lib', ''));

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

    $defaultTimeZone = 'UTC';
    try {
      $defaultTimeZone = @date_default_timezone_get();
    }
    catch (ErrorException $e) { }
    
    $this->config->defaults = array(
      'core' => array(
        'language' => $this->appConfig['defaultLanguage'],
        'timeZone' => $defaultTimeZone,
        'showExceptions' => false,
        'logLevel' => Logger::ALL,
        'createCrashReports' => true
      ),
    );
    
    $this->config->defaults = $this->defaultConfig;

    Logger::attachFile(
      $this->p('log', $this->environment . '.log'),
      $this->config['core']['logLevel']
    );

    // I18n system
    I18n::setup($this->config['core'], $this->paths->languages);

    // Error handling
    ErrorReporting::setHandler(array($this, 'handleError'));
    
    // Import modules
    $this->triggerEvent('beforeImportModules');
    $modules = array();
    foreach ($this->import as $module) {
      Lib::import($module);
      $segments = explode('/', $module);
      $name = $segments[count($segments) - 1];
      $this->paths->$name = LIB_PATH . '/' . implode('/', $segments);
      $modules[] = $name;
    }
    $this->triggerEvent('afterImportModules');
    
    // Load application listeners
    foreach ($this->listenerNames as $listener) {
      Lib::assumeSubclassOf($listener, 'AppListener');
      $this->attachEventListener(new $listener($this));
    }

    // Load modules
    $this->triggerEvent('beforeLoadModules');
    foreach ($modules as $module) {
      $object = $this->getModule($module);
    }
    $this->triggerEvent('afterLoadModules');
    
    $this->triggerEvent('afterInit');
  }
  
  /**
   * Stop application (exit PHP execution)
   * @param int $status Return code
   */
  public function stop($status = 0) {
    exit($status);
  }
}

/**
 * Event sent before and after a module has been loaded
 */
class LoadModuleEvent extends LoadEvent { }
