<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Routing\Http;
use Jivoo\Core\Store\PhpStore;
use Jivoo\Core\Store\Config;
use Jivoo\Core\Store\StateMap;
use Jivoo\Core\Store\Jivoo\Core\Store;

/**
 * Application class for initiating Jivoo applications.
 * @property string $basePath Web base path.
 * @property-read PathDictionary $paths Paths.
 * @property-read string $name Application name.
 * @property-read string $version Application version.
 * @property-read string $namespace Application namespace.
 * @property-read string $minPhpVersion Minimum PHP version.
 * @property-read string $environment Environment name.
 * @property-read Config $config User configuration.
 * @property-read bool $noManifest True if application manifest file missing.
 * @property-read array $manifest Application manifest.
 * @property-read string $sessionPrefix Application session prefix.
 * @property-read string $entryScript Name of entry script, e.g. 'index.php'.
 * @property-read EventManager $eventManager Application event manager.
 * @property-read StateMap $state Application persistent state storage.
 */
class App implements IEventSubject {
  /**
   * @var array Application configuration.
   */
  private $manifest = array();
  
  /**
   * @var array Default application configuration.
   */
  private $defaultManifest = array(
    'name' => 'Jivoo Application',
    'version' => '0.0.0',
    'namespace' => 'App',
    'minPhpVersion' => '5.3.0',
    'modules' => array(
      'Snippets', 'Routing', 'Assets',
      'View', 'Models', 'Helpers', 'Extensions',
      'Themes', 'Jtk', 'Setup', 'Console'
    ),
    'listeners' => array(),
    'defaultLanguage' => 'en',
    'sessionPrefix' => '',
    'defaultConfig' => array()
  );
  
  /**
   * @var bool True if app manifest missing.
   */
  private $noManifest = false;

  /**
   * @var Config User configuration.
   */
  private $config = null;
  
  /**
   * @var array Default user configuration.
   */
  private $defaultConfig = array();

  /**
   * @var PathMap Paths.
   */
  private $paths = null;

  /**
   * @var string Web base path.
   */
  private $basePath = '/';
  
  /**
   * @var string Entry script name.
   */
  private $entryScript = 'index.php';

  /**
   * @var string Application name.
   */
  private $name;

  /**
   * @var string Application version.
   */
  private $version;
  
  /**
   * @var string Application namespace.
   */
  private $namespace;

  /**
   * @var string Minimum PHP version.
   */
  private $minPhpVersion;

  /**
   * @var string[] List of modules to load.
   */
  private $modules;
  
  /**
   * @var string[] List of application listener names.
   */
  private $listenerNames = array();

  /**
   * @var ModuleMap Map of modules.
   */
  private $m = null;
  
  /**
   * @var string[] Module load list.
   */
  private $imports = array();

  /**
   * @var callback[][] Associative array mapping module names to a list of
   * callbacks.
   */
  private $waitingCalls = array();
  
  /**
   * @var string Environment name.
   */
  private $environment = 'production';
  
  /**
   * @var StateMap
   */
  private $state = null;
  
  /**
   * @var array Associative array of default environment configurations.
   */
  private $defaultEnvironments = array(
    'production' => array(
      'core' => array(
        'showExceptions' => false,
        'logLevel' => Logger::ERROR,
        'createCrashReports' => true,
      )
    ),
    'development' => array(
      'core' => array(
        'showExceptions' => true,
        'logLevel' => Logger::ALL,
        'createCrashReports' => false,
      )
    )
  );

  /**
   * @var string Application session prefix.
   */
  private $sessionPrefix = 'jivoo_';
  
  /**
   * @var string[] Names of events produced by this object.
   */
  private $events = array(
    'beforeImportModules', 'afterImportModules', 'beforeLoadModules',
    'beforeLoadModule', 'afterLoadModule', 'afterLoadModules', 'afterInit',
    'beforeShowException', 'beforeStop'
  );
  
  /**
   * @var string[][] Associative array of module names and lists of optional
   * dependencies.
   */
  private $optionalDependencies = array();
  
  /**
   * @var EventManager Application event manager.
   */
  private $e = null;

  /**
   * Create application.
   * @param string $appPath Path to app-directory containing at least an
   * 'app.json' configuration file.
   * @param string $userPath Path to user-directory.
   * @param string $entryScript Name of entry script, e.g. 'index.php'.
   * @throws \Exception In application configuration is missing or invalid.
   */
  public function __construct($appPath, $userPath, $entryScript = 'index.php') {
    $appPath = Utilities::convertPath($appPath);
    $userPath = Utilities::convertPath($userPath);
    $manifestFile = $appPath . '/app.json';
    $manifest = array();
    if (file_exists($manifestFile)) {
      $manifest = Json::decodeFile($manifestFile);
      $manifest = array_merge($this->defaultManifest, $manifest);
    }
    else {
      Logger::error('Invalid application. "app.json" not found. Configuring default application.');
      $this->noManifest = true;
      $manifest = $this->defaultManifest;
    }
    $this->manifest = $manifest;
    $this->e = new EventManager($this);
    $this->m = new ModuleMap();
    $this->paths = new PathMap(
      dirname($_SERVER['SCRIPT_FILENAME']),
      $userPath
    );
    $this->paths->app = $appPath;
    $this->paths->user = $userPath;
//     $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
    $this->entryScript = $entryScript;
    
    // Temporary work-around for weird SCRIPT_NAME.
    // When url contains a trailing dot such as
    // /app/index.php/admin./something
    // SCRIPT_NAME returns /PeanutCMS/index.php/admin./something instead of expected
    // /app/index.php
    $script = explode('/', $_SERVER['SCRIPT_NAME']);
    while (count($script) > 0) {
      if ($script[count($script) - 1] == $entryScript) {
        break;
      }
      array_pop($script);
    }
    $this->basePath = dirname(implode('/', $script));
    // END work-around
    
    $this->name = $manifest['name'];
    $this->version = $manifest['version'];
    $this->namespace = $manifest['namespace'];
    $this->minPhpVersion = $manifest['minPhpVersion'];
    $this->modules = $manifest['modules'];
    $this->sessionPrefix = $manifest['sessionPrefix'];
    $this->listenerNames = $manifest['listeners'];
    $this->defaultConfig = $manifest['defaultConfig'];

    Lib::import($this->p('app', 'lib'), $this->namespace);
    
    $this->paths->Core = \Jivoo\PATH . '/Jivoo/Core';

    $file = new PhpStore($this->p('user', 'config.php'));
    $this->config = new Config($file);
//     $this->config->setVirtual('app', $this->manifest);
  }

  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    switch ($property) {
      case 'paths':
      case 'name':
      case 'version':
      case 'namespace':
      case 'minPhpVersion':
      case 'environment':
      case 'config':
      case 'manifest':
      case 'noManifest':
      case 'sessionPrefix':
      case 'basePath':
      case 'entryScript':
      case 'state':
        return $this->$property;
      case 'eventManager':
        return $this->e;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Set value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'basePath':
        $this->$property = $value;
        return;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * {@inheritdoc}
   */
  public function getEvents() {
    return $this->events;
  }

  /**
   * {@inheritdoc}
   */
  public function attachEventHandler($name, $callback) {
    $this->e->attachHandler($name, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function attachEventListener(IEventListener $listener) {
    $this->e->attachListener($listener);
  }

  /**
   * {@inheritdoc}
   */
  public function detachEventHandler($name, $callback) {
    $this->e->detachHandler($name, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function detachEventListener(IEventListener $listener) {
    $this->e->detachListener($listener);
  }

  /**
   * {@inheritdoc}
   */
  public function hasEvent($name) {
    return in_array($name, $this->events);
  }

  /**
   * Trigger an event on this object.
   * @param string $name Name of event.
   * @param Event $event Event object.
   * @return bool False if event was stopped, true otherwise.
   */
  private function triggerEvent($name, Event $event = null) {
    return $this->e->trigger($name, $event);
  }

  /**
   * Get the absolute path of a file.
   * @param string $key Location-identifier.
   * @param string $path File.
   * @return string Absolute path.
   */
  public function p($key, $path = '') {
    return $this->paths->$key . '/' . $path;
  }

  /**
   * Get the absolute path of a file relative to the public directory.
   * @param string $path File.
   * @return string Path.
   */
  public function w($path = '') {
    if ($this->basePath == '/') {
      return '/' . $path;
    }
    return $this->basePath . '/' . $path;
  }
  
  /**
   * Prepend application namespace to a name.
   * @param string $name Name.
   * @return string Name.
   */
  public function n($name = '') {
    if ($this->namespace == '')
      return $name;
    if ($name == '')
      return $this->namespace;
    return $this->namespace . '\\' . $name;
  }
  
  /**
   * Add a module to the module load list.
   * @param string $name Module name.
   */
  public function addModule($name) {
    $this->modules[] = $name;
  }
  
  /**
   * Stop a module from loading.
   * @param string $name Module name.
   */
  public function removeModule($name) {
    $this->modules = array_diff($this->modules, array($name));
  }
  
  /**
   * Import module.
   * @param string $module Module name.
   */
  public function import($module) {
    if (strpos($module, '\\') === false) {
      $name = $module;
      $module = 'Jivoo\\' . $name . '\\' . $name;
      $pathName = $name;
    }
    else {
      $segments = explode('\\', $module);
      $name = array_pop($segments);
      if ($segments == array('Jivoo', $name))
        $pathName = $name;
      else
        $pathName = $module;
    }
    $this->paths->$pathName = dirname(\Jivoo\PATH . '/' . str_replace('\\', '/', $module));
    $this->imports[$name] = $module;
    $this->optionalDependencies = LoadableModule::getLoadOrder($module, $this->optionalDependencies);
  }
  
  /**
   * Get a module, or load it if not yet loaded (must be imported however).
   * @param string $name Name of module class.
   * @return LoadableModule Module object.
   */
  public function getModule($name) {
    if (!isset($this->m->$name)) {
      $this->triggerEvent('beforeLoadModule', new LoadModuleEvent($this, $name));
      if (!isset($this->imports[$name]))
        throw new \Exception(tr('Module not imported: %1', $name));
      $module = $this->imports[$name];
      if (isset($this->optionalDependencies[$name])) {
        foreach ($this->optionalDependencies[$name] as $dependency) {
          if (isset($this->imports[$dependency]))
            $this->getModule($dependency);
        }
      }
      Lib::assumeSubclassOf($module, 'Jivoo\Core\LoadableModule');
      $this->m->$name = new $module($this);
      $this->triggerEvent('afterLoadModule', new LoadModuleEvent($this, $name, $this->m->$name));
      $this->m->$name->afterLoad();
      if (isset($this->waitingCalls[$name])) {
        foreach ($this->waitingCalls[$name] as $tuple) {
          list($method, $args) = $tuple;
          call_user_func_array(array($this->m->$name, $method), $args);
        }
      }
    }
    return $this->m->$name;
  }
  
  /**
   * Load several modules.
   * @param string[] $modules List of module names.
   * @return ModuleMap A map of all loaded modules.
   */
  public function getModules($modules) {
    foreach ($modules as $name) {
      $this->getModule($name);
    }
    return $this->m;
  }
  
  /**
   * Whether or not a module has been loaded.
   * @param string $name Module name.
   * @return bool True if loaded, false otherwise.
   */
  public function hasModule($name) {
    return isset($this->m->$name);
  }
  
  /**
   * Whether or not a module will be loaded.
   * @param string $name Module name.
   * @return bool True if on import list, false otherwise.
   */
  public function hasImport($name) {
    return isset($this->imports[$name]);
  }

  /**
   * Call a method in a module immediately if the module has been loaded, or
   * whenever the module is loaded if it has not.
   * @param string $module Module name.
   * @param string $method Method name.
   * @param mixed $parameters,... Paremeters to method.
   * @return mixed|null Returned value, or null if module not yet loaded.
   */
  public function call($module, $method) {
    $args = func_get_args();
    $args = array_slice($args, 2);
    if (isset($this->m->$module))
      return call_user_func_array(array($this->m->$module, $method), $args);
    if (!isset($this->waitingCalls[$module]))
      $this->waitingCalls[$module] = array();
    $this->waitingCalls[$module][] = array($method, $args);
    return null;
  }
  
  /**
   * Output an HTML crash report based on an exception. Can use a custom
   * template stored in 'app/templates/error/exception.php'.
   * @param \Exception $exception \Exception to report.
   */
  public function crashReport(\Exception $exception) {
    $app = $this->name;
    $version = $this->version;
    $title = tr('Uncaught exception');
    $custom = null;
    try {
      $custom = $this->p('app', 'templates/error/exception.php');
      if (!file_exists($custom))
        $custom = null;
      else
        include $custom;
    }
    catch (\Exception $e) { }
    if (!isset($custom))
      include \Jivoo\PATH . '/Jivoo/Core/templates/error/exception.php';
  }
  
  /**
   * Handler for uncaught exceptions.
   * @param \Exception $exception The exception.
   */
  public function handleError(\Exception $exception) {
    if ($this->config['core']['createCrashReports']) {
      $file = $exception->getFile();
      $line = $exception->getLine();
      $message = $exception->getMessage();
      $hash = substr(md5($file . $line . $message), 0, 10);
      $name = date('Y-m-d') . '_crash_' . $hash . '.html';
      if (!file_exists($this->p('log', $name))) {
        $file = fopen($this->p('log', $name), 'w');
        if ($file !== false) {
          ob_start();
          $this->crashReport($exception);
          fwrite($file, ob_get_clean());
          fclose($file);
          Logger::error(tr('A crash report has been generated: "%1"', $name));
        }
        else {
          $hash = null;
          Logger::error(tr('Failed to create crash report "%1"', $name));
        }
      }
      if (!$this->config['core']['showReference'])
        $hash = null;
    }
    // Clean the view
    while (ob_get_level() > 0)
      ob_end_clean(); 
    Http::setContentType('text/html');
    Http::setStatus(Http::INTERNAL_SERVER_ERROR);
    if ($this->config['core']['showExceptions']) {
      ob_start();
      $this->crashReport($exception);
      $body = ob_get_clean();
      $event = new ShowExceptionEvent($this, $exception, $body);
      $this->triggerEvent('beforeShowException', $event);
      echo $event->body;
      $this->stop();
    }
    else {
      $custom = null;
      try {
        $custom = $this->p('app', 'templates/error/error.php');
        if (!file_exists($custom))
          $custom = null;
        else
          include $custom;
      }
      catch (\Exception $e) { }
      if (!isset($custom))
        include \Jivoo\PATH . '/Jivoo/Core/templates/error/error.php';
      $this->stop();
    }
  }

  /**
   * Run the application.
   * @param string $environment Configuration environment to use, e.g.
   * 'production' or 'development'. Environments are stored in
   * 'app/config/environments'.
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

    $environmentConfigFile = $this->p('app', 'config/environments/' . $environment . '.php');
    if (file_exists($environmentConfigFile)) {
      $this->config->override = include $environmentConfigFile;
    }
    else {
      if (isset($this->defaultEnvironments[$environment])) {
        $this->config->override = $this->defaultEnvironments[$environment];
      }
      Logger::notice(
        'Configuration file for environment "' . $environment . '" not found'
      );
    }

    $this->config->defaults = array(
      'core' => array(
        'language' => $this->manifest['defaultLanguage'],
        'showExceptions' => false,
        'logLevel' => Logger::ALL,
        'createCrashReports' => true,
        'showReference' => true
      ),
    );
    
    if (!isset($this->config['core']['timeZone'])) {
      $defaultTimeZone = 'UTC';
      try {
        $defaultTimeZone = @date_default_timezone_get();
      }
      catch (\ErrorException $e) { }
      $this->config['core']['timeZone'] = $defaultTimeZone;
    }
    
    $this->config->defaults = $this->defaultConfig;

    Logger::attachFile(
      $this->p('log', $this->environment . '.log'),
      $this->config['core']['logLevel']
    );
    register_shutdown_function(array('Jivoo\Core\Logger', 'saveAll'));

    // I18n system
    I18n::setup($this->config['core'], $this->paths->languages);

    // Error handling
    ErrorReporting::setHandler(array($this, 'handleError'));
    
    // Persistent state storage
    $this->state = new StateMap($this->p('state', ''));

    // Import modules
    $this->triggerEvent('beforeImportModules');
    foreach ($this->modules as $module) {
      $this->import($module);
    }
    $this->triggerEvent('afterImportModules');
    
    // Load application listeners
    foreach ($this->listenerNames as $listener) {
      $listener = $this->n($listener);
      Lib::assumeSubclassOf($listener, 'Jivoo\Core\AppListener');
      $this->attachEventListener(new $listener($this));
    }
    
    // Load modules
    $this->triggerEvent('beforeLoadModules');
    foreach ($this->imports as $name => $module) {
      $object = $this->getModule($name);
    }
    $this->triggerEvent('afterLoadModules');
    
    $this->triggerEvent('afterInit');
    
    Logger::warning(tr('Application not stopped'));
  }
  
  /**
   * Stop application (exit PHP execution). Use instead of {@see exit}.
   * @param int $status Return code
   */
  public function stop($status = 0) {
    $this->triggerEvent('beforeStop');
    
    $open = $this->state->closeAll();
    if (!empty($open))
      Logger::warning(tr('The following state documents were not properly closed: %1{, }{ and }', $open));
    exit($status);
  }
}

/**
 * Event sent before and after a module has been loaded
 */
class LoadModuleEvent extends LoadEvent { }

/**
 * Event sent before an exception page is sent to the client.
 */
class ShowExceptionEvent extends Event {
  
  /**
   * @var \Exception The exception.
   */
  public $exception;
  
  /**
   * @var string The response body.
   */
  public $body;
  
  /**
   * Construct exception event.
   * @param object $sender Sender object.
   * @param \Exception $exception The exception.
   * @param string $body The response body.
   */
  public function __construct($sender, \Exception $exception, $body) {
    parent::__construct($sender);
    $this->exception = $exception;
    $this->body = $body;
  }
}
