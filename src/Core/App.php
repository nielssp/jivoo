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
use Psr\Log\LoggerAwareInterface as LoggerAware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Jivoo\Core\Log\FileLogger;
use Jivoo\Core\Log\LogException;
use Jivoo\Core\Vendor\VendorLoader;
use Jivoo\Core\Log\ErrorHandler;
use Jivoo\Core\Log\Logger;
use Jivoo\Core\Log\FileHandler;
use Jivoo\Core\Log\ErrorException;
use Jivoo\Core\Cli\Shell;
use Psr\Cache\CacheItemPoolInterface;
use Jivoo\Core\Cache\NullPool;
use Jivoo\Core\Store\Document;

/**
 * Application class for initiating Jivoo applications.
 * @property string $basePath Web base path.
 * @property-read Paths $paths Application and framework paths.
 * @property-read string $name Application name.
 * @property-read string $version Application version.
 * @property-read string $environment Environment name.
 * @property-read Config $config User configuration.
 * @property-read bool $noManifest True if application manifest file missing.
 * @property-read array $manifest Application manifest.
 * @property-read string $entryScript Name of entry script, e.g. 'index.php'.
 * @property-read EventManager $eventManager Application event manager.
 * @property-read LoggerInterface $logger Application logger.
 * @property-read ModuleLoader $m Module loader.
 */
class App extends EventSubjectBase implements LoggerAware {
  /**
   * @var array Application configuration.
   */
  private $manifest = array();
  
  /**
   * @var bool A fatal error has been encountered.
   */
  private $fatalError = false;
  
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
   * @var ModuleLoader Module loader.
   */
  private $m = null;
  
  /**
   * @var string Environment name.
   */
  private $environment = 'production';
  
  /**
   * @var string[] Names of events produced by this object.
   */
  protected $events = array('init', 'ready', 'showException', 'stop');
  
  /**
   * @var LoggerInterface Application logger.
   */
  private $logger;
  
  /**
   * @var string[]
   */
  private $errorPaths = array();
  
  /**
   * Create application.
   * @param string $appPath Path to app-directory containing at least an
   * 'app.json' configuration file.
   * @param string $userPath Path to user-directory.
   * @param string $entryScript Name of entry script, e.g. 'index.php'.
   */
  public function __construct($appPath, $userPath, $entryScript = 'index.php') {
    parent::__construct();
    $this->logger = ErrorHandler::getInstance()->getLogger();

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
    $this->m = new ModuleLoader();
    $this->paths = new Paths(
      Paths::convertPath(getcwd()),
      $userPath
    );
    $this->paths->app = $appPath;
    $this->paths->user = $userPath;
//     $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
    $this->entryScript = $entryScript;
    
    // Temporary work-around for weird SCRIPT_NAME.
    // When url contains a trailing dot such as
    // /app/index.php/admin./something
    // SCRIPT_NAME returns /app/index.php/admin./something instead of expected
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

    Autoloader::getInstance()->addPath($this->namespace, $this->p('app'));

    $this->paths->Jivoo = \Jivoo\PATH;
    $this->paths->Core = \Jivoo\PATH . '/Core';

    $file = new PhpStore($this->p('user/config.php'));
    $this->config = new Document();
    $this->config['user'] = new Config($file);
  }

  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    switch ($property) {
      case 'name':
      case 'version':
      case 'paths':
      case 'environment':
      case 'config':
      case 'manifest':
      case 'noManifest':
      case 'basePath':
      case 'entryScript':
      case 'logger':
      case 'm':
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
   * Whether a property is set.
   * @param string $property Property name.
   * @return bool True if property set.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __isset($property) {
    return !is_null($this->__get($property));
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
   * Trigger an event on this object.
   * @param string $name Name of event.
   * @param Event $event Event object.
   * @return bool False if event was stopped, true otherwise.
   */
  private function safeTriggerEvent($name, Event $event = null) {
    if ($this->fatalError)
      return;
    try {
      ob_start();
      $this->e->trigger($name, $event);
      ob_end_clean();
    }
    catch (\Exception $e) {
      $this->logger->alert(
        tr('An event handler for "%1" threw an exception: %2', '{name}', $e->getMessage()),
        array('name' => $name, 'exception' => $e)
      );
    }
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
    if ($exception instanceof \ErrorException)
      $title = tr('Fatal error (%1)', ErrorHandler::toString($exception->getSeverity()));
    else
      $title = tr('Uncaught exception');
    $log = array();
    if ($this->logger instanceof \Jivoo\Core\Log\Logger)
      $log = $this->logger->getLog();
    $custom = null;
    try {
      if (isset($this->errorPaths['exceptionTemplate'])) {
        include $this->errorPaths['exceptionTemplate'];
        $custom = true;
      }
    }
    catch (\Exception $e) {
      $this->logger->alert(
        tr('Exception template (%1) failed: %2', '{template}', $e->getMessage()),
        array('template' => $this->errorPaths['exceptionTemplate'], 'exception' => $e)
      );
    }
    if (!isset($custom))
      include \Jivoo\PATH . '/Core/templates/error/exception.php';
  }
  
  /**
   * Handler for uncaught exceptions.
   * @param \Exception $exception The exception.
   * @param bool Whether the exception was generated by a fatal PHP error.
   */
  public function handleError($exception, $fatal = false) {
    $this->logger->critical(
      tr('Uncaught exception: %1', $exception->getMessage()),
      array('exception' => $exception)
    );
    $this->fatalError = $fatal;
    if ($this->isCli()) {
      if (isset($this->m->shell)) {
        $this->m->shell->handleException($exception);
      }
      else {
        Shell::dumpException($exception);
      }
      $this->stop(1);
    }
    if ($this->config['system']['createCrashReports']) {
      $file = $exception->getFile();
      $line = $exception->getLine();
      $message = $exception->getMessage();
      $hash = substr(md5($file . $line . $message), 0, 10);
      $name = date('Y-m-d') . '_crash_' . $hash . '.html';
      if (!isset($this->errorPaths['log'])) {
        $this->logger->alert(tr('Could not create crash report: Log directory is missing'));
      }
      else if (!file_exists($this->errorPaths['log'] . '/' . $name)) {
        $file = fopen($this->errorPaths['log'] . '/' . $name, 'w');
        if ($file !== false) {
          ob_start();
          $this->crashReport($exception);
          fwrite($file, ob_get_clean());
          fclose($file);
          $this->logger->critical(tr('A crash report has been generated: "%1"', $name));
        }
        else {
          $hash = null;
          $this->logger->alert(tr('Failed to create crash report "%1"', $name));
        }
      }
      if (!$this->config['system']['showReference'])
        $hash = null;
    }
    // Clean the view
    while (ob_get_level() > 0)
      ob_end_clean();
    Http::setContentType('text/html');
    Http::setStatus(Http::INTERNAL_SERVER_ERROR);
    if ($this->config['system']['showExceptions']) {
      ob_start();
      $this->crashReport($exception);
      $body = ob_get_clean();
      $event = new ShowExceptionEvent($this, $exception, $body);
      $this->safeTriggerEvent('showException', $event);
      echo $event->body;
      $this->stop(1);
    }
    else {
      $custom = null;
      try {
        if (isset($this->errorPaths['errorTemplate'])) {
          include $this->errorPaths['errorTemplate'];
          $custom = true;
        }
      }
      catch (\Exception $e) {
        $this->logger->alert(
          tr('Error template (%1) failed: %2', '{template}', $e->getMessage()),
          array('template' => $this->errorPaths['errorTemplate'], 'exception' => $e)
        );
      }
      if (!isset($custom))
        include \Jivoo\PATH . '/Core/templates/error/error.php';
      $this->stop(1);
    }
  }
  
  /**
   * Handle a fatal PHP error in the same way as uncaught exceptions: By logging
   * the error and presenting an error page.
   */
  public function handleFatalError() {
    $error = error_get_last();
    if ($error) {
      switch ($error['type']) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
          $this->handleError(new ErrorException(
            $error['message'], 0, $error['type'], $error['file'], $error['line']
          ), true);
      }
    }
  }

  /**
   * Run the application.
   * @param string $environment Configuration environment to use, e.g.
   * 'production' or 'development'. Environments are stored in
   * 'app/config/environments'.
   * @param bool $enableCli Whether to enable the command-line interface. If
   * enabled, the environment will be set to 'cli' when the application is run
   * from the command-line.
   */
  public function run($environment = 'production', $enableCli = true) {
    if ($this->isCli()) {
      if (!$enableCli) {
        echo tr('The command-line interface is disabled.');
        $this->stop();
      }
      $environment = 'cli';
    }
    $this->environment = $environment;

    // Precompute paths used for error handling
    $logDir = $this->p('log');
    if (Utilities::dirExists($logDir))
      $this->errorPaths['log'] = realpath($logDir);
    $errorTemplate = $this->p('app/templates/error/error.php');
    if (file_exists($errorTemplate))
      $this->errorPaths['errorTemplate'] = realpath($errorTemplate);
    $exceptionTemplate = $this->p('app/templates/error/exception.php');
    if (file_exists($exceptionTemplate))
      $this->errorPaths['exceptionTemplate'] = realpath($exceptionTemplate);
    
    // Set exception handler
    set_exception_handler(array($this, 'handleError'));
    
    // Set fatal error handler
    register_shutdown_function(array($this, 'handleFatalError'));
    
    // Force output buffereing (so that error-pages can clear it)
    ob_start();

    // Load default configuration from application and/or Core
    if (file_exists($this->p('app/config/default.php')))
      $this->config->defaults = include $this->p('app/config/default.php');
    $this->config->defaults = include $this->p('Core/config/default.php');
    
    // Set timezone (required by file logger)
    if (!isset($this->config['user']['i18n']['timeZone'])) {
      $defaultTimeZone = 'UTC';
      $error = ErrorHandler::detect(function() use($defaultTimeZone) {
        $defaultTimeZone = @date_default_timezone_get();
      });
      $this->config['user']['i18n']['timeZone'] = $defaultTimeZone;
    }
    if (!date_default_timezone_set($this->config['user']['i18n']['timeZone']))
      date_default_timezone_set('UTC');

    // Set up the default file loger    
    if ($this->logger instanceof Logger) {
      try {
        $this->logger->addHandler(new FileHandler(
          $this->p('log/' . $this->environment . '.log'),
          $this->config['system']->get('logLevel', LogLevel::WARNING)
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
    if (version_compare(phpversion(), $this->manifest['minPhpVersion']) < 0) {
      throw new AppException(tr(
        '%1 does not support PHP %2. PHP %3 or above is required',
        $this->name, phpversion(), $this->minPhpVersion
      ));
    }
    
    $class = $this->n('Init');
    if (!class_exists($class))
      $class = 'Jivoo\Core\Init';
    $this->m->init = new $class($this);

    $this->triggerEvent('init');
    $this->m->init->init($environment);
    $this->triggerEvent('ready');
    
    $this->logger->warning(tr('Application not stopped'));
    $this->stop(1);
  }
  
  /**
   * Stop application (exit PHP execution). Use instead of {@see exit}.
   * @param int $status Return code
   */
  public function stop($status = 0) {
    $this->safeTriggerEvent('stop');
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
