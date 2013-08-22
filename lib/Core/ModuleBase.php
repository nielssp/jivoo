<?php
/**
 * Base class for modules
 * @package Core
 */
abstract class ModuleBase {

  /**
   * @var App The application that has loaded this module
   */
  protected $app = null;

  /**
   * @var Dictionary Other modules
   */
  protected $m = null;
  
  /**
   * @var AppConfig Module configuration
   */
  protected $config = null;

  /**
   * @var Request|null The Request object if available
   */

  protected $request = null;
  /**
   * @var Session|null Session storage object if available
   */
  protected $session = null;

  /**
   * Module constructor
   * @param ModuleBase[] $modules An array of modules
   * @param App $app The application object 
   * @param mixed $var,... Additional arguments to relay to init() method
   */
  public final function __construct($modules, App $app) {
    $this->m = new Dictionary($modules, true);
    $this->app = $app;
    $this->config = $app->config[get_class($this)];

    if (isset($this->m->Routing)) {
      $this->request = $this->m->Routing->getRequest();
      $this->session = $this->request->session;
    }

    $additionalParameters = func_get_args();
    array_shift($additionalParameters);
    array_shift($additionalParameters);

    call_user_func_array(array($this, 'init'), $additionalParameters);
  }

  /**
   * Get the absolute path of a file.
   * If called with a single parameter, then the name of the current module
   * is used as location identifier.
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path = null) {
    if (isset($path)) {
      return $this->app
        ->p($key, $path);
    }
    else {
      return $this->app
        ->p(get_class($this), $key);
    }
  }

  /**
   * Get the absolute path of a file relative to the public directory
   * @param string $path File
   * @return string Path
   */
  public function w($path = '') {
    return $this->app
      ->w($path);
  }

  /**
   * Module initialisation
   */
  protected abstract function init();
}
