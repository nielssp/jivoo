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
use Jivoo\InvalidPropertyException;
use Jivoo\InvalidClassException;
use Jivoo\Autoloader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Jivoo\Core\Log\FileLogger;
use Jivoo\Core\Log\LogException;
use Jivoo\Core\Vendor\VendorLoader;
use Jivoo\Core\Log\ErrorHandler;
use Jivoo\Core\Log\Logger;
use Jivoo\Core\Log\FileHandler;

/**
 * Application class for initiating Jivoo applications.
 * @property string $basePath Web base path.
 * @property-read Paths $paths Application and framework paths.
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
 * @property-read LoggerInterface $logger Application logger.
 * @property-read ModuleLoader $m Module loader.
 * @property-read VendorLoader $vendor Third-party library loader.
 */
class App implements IEventSubject, LoggerAwareInterface {
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
   * @var Paths Application and framework paths.
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
   * @var ModuleLoader Module loader.
   */
  private $m = null;
  
  /**
   * @var string Environment name.
   */
  private $environment = 'production';
  
  /**
   * @var StateMap
   */
  private $state = null;

  /**
   * @var string Application session prefix.
   */
  private $sessionPrefix = 'jivoo_';
  
  /**
   * @var string[] Names of events produced by this object.
   */
  private $events = array(
    'beforeBoot', 'afterBoot',
    'beforeImportModules', 'afterImportModules', 'beforeLoadModules',
    'afterLoadModules', 'afterInit',
    'beforeShowException', 'beforeStop'
  );
  
  /**
   * @var EventManager Application event manager.
   */
  private $e = null;
  
  /**
   * @var VendorLoader Third-party library loader.
   */
  private $vendor;
  
  /**
   * @var LoggerInterface Application logger.
   */
  private $logger;

  /**
   * Create application.
   * @param string $appPath Path to app-directory containing at least an
   * 'app.json' configuration file.
   * @param string $userPath Path to user-directory.
   * @param string $entryScript Name of entry script, e.g. 'index.php'.
   */
  public function __construct($appPath, $userPath, $entryScript = 'index.php') {
    $this->logger = ErrorHandler::getInstance()->getLogger();
    // TODO: deprecated static logger
    \Jivoo\Core\Logger::setLogger($this->logger);

    $appPath = Utilities::convertPath($appPath);
    $userPath = Utilities::convertPath($userPath);
    $manifestFile = $appPath . '/app.json';
    if (file_exists($manifestFile)) {
      $manifest = Json::decodeFile($manifestFile);
      $manifest = array_merge($this->defaultManifest, $manifest);
    }
    else {
      $this->logger->error('Invalid application. "app.json" not found. Configuring default application.');
      $this->noManifest = true;
      $manifest = $this->defaultManifest;
    }
    $this->manifest = $manifest;
    $this->e = new EventManager($this);
    $this->m = new ModuleLoader($this);
    $this->paths = new Paths(
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
    $this->sessionPrefix = $manifest['sessionPrefix'];

    Autoloader::getInstance()->addPath($this->namespace, $this->p('app'));

    $this->paths->Jivoo = \Jivoo\PATH;
    $this->paths->Core = \Jivoo\PATH . '/Core';

    $file = new PhpStore($this->p('user/config.php'));
    $this->config = new Config($file);
    
    // Persistent state storage
    $this->state = new StateMap($this->p('state'));
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
      case 'logger':
      case 'm':
      case 'vendor':
        return $this->$property;
      case 'eventManager':
        return $this->e;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
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
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }
  
  /**
   * Whether application is running from the command line.
   * @return bool True if CLI.
   */
  public function isCli() {
    return php_sapi_name() == 'cli';
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
   * @param string $ipath Internal path, see {@see Paths}.
   * @param string $path File.
   * @return string Absolute path.
   */
  public function p($ipath, $path = null) {
    if (isset($path)) {
//       trigger_error(tr('The second parameter of p() is no longer needed'), E_USER_DEPRECATED);
      return $this->paths->p($ipath . '/' . $path);
    }
    return $this->paths->p($ipath);
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
   * Output an HTML crash report based on an exception. Can use a custom
   * template stored in 'app/templates/error/exception.php'.
   * @param \Exception $exception \Exception to report.
   */
  public function crashReport(\Exception $exception) {
    $app = $this->name;
    $version = $this->version;
    $title = tr('Uncaught exception');
    $log = array();
    if ($this->logger instanceof \Jivoo\Core\Log\FileLogger)
      $log = $this->logger->getLog();
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
      include \Jivoo\PATH . '/Core/templates/error/exception.php';
  }
  
  /**
   * Handler for uncaught exceptions.
   * @param \Exception $exception The exception.
   */
  public function handleError($exception) {
    $this->logger->critical(
      tr('Uncaught exception: %1', $exception->getMessage()),
      array('exception' => $exception)
    );
    if ($this->isCli()) {
      echo 'Exception: ' . $exception->getMessage();
      $this->stop();
    }
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
        include \Jivoo\PATH . '/Core/templates/error/error.php';
      $this->stop();
    }
  }
  
  /**
   * 
   */
  public function handleFatalError() {
    $error = error_get_last();
    if ($error) {
      echo 'lol fatal: ';
      echo $error['message'];
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

    // Error handling
    
    // Throw exceptions instead of fatal errors on class not found
    spl_autoload_register(function($class) {
      throw new InvalidClassException(tr('Class not found: %1', $class));
    });
    
    // Set exception handler
    set_exception_handler(array($this, 'handleError'));

    if ($this->logger instanceof Logger) {
      try {
        $this->logger->addHandler(new FileHandler(
          $this->p('log/' . $this->environment . '.log'),
          $this->config['core']->get('logLevel', LogLevel::WARNING)
        ));
      }
      catch (LogException $e) {
        $this->logger->warning($e->getMessage(), array('exception' => $e));
        $this->logger->addHandler(new FileHandler(
          $this->p('log/' . $this->environment . '.log'),
          LogLevel::WARNING
        ));
      }
    }

    // Check PHP version
    if (version_compare(phpversion(), $this->minPhpVersion) < 0) {
      throw new AppException(tr(
        '%1 does not support PHP %2. PHP %3 or above is required',
        $this->name, phpversion(), $this->minPhpVersion
      ));
    }
    
    // Find initialization class
    $class = $this->n('Boot');
    if (!Utilities::classExists($class))
      $class = 'Jivoo\Core\Boot';
    $boot = new $class($this);
    $this->triggerEvent('beforeBoot');
    $boot->boot($environment);
    $this->triggerEvent('afterBoot');
    $this->triggerEvent('afterLoadModules'); // TODO: legacy event
    $this->triggerEvent('afterInit'); // TODO: legacy event
    
    $this->logger->warning(tr('Application not stopped'));
  }
  
  /**
   * Stop application (exit PHP execution). Use instead of {@see exit}.
   * @param int $status Return code
   */
  public function stop($status = 0) {
    $this->triggerEvent('beforeStop');
    
    $open = $this->state->closeAll();
    if (!empty($open))
      $this->logger->warning(tr(
        'The following state documents were not properly closed: %1{, }{ and }',
        $open
      ));
    
    exit($status);
  }
}

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
