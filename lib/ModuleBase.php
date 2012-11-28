<?php
/**
 * Base class for modules
 * @package PeanutCMS
 */
abstract class ModuleBase {
  
  /**
   * @deprecated Core replaced with App
   * @var Core The Core object
   */
  protected $Core = null;

  /**
   * @var App The associated application
   */
  protected $app = null;

  /**
   * @var Dictionary Other modules
   */
  protected $m = null;

  /**
   * @var Request|null The Request object if available
   */

  protected $request = null;
  /**
   * @var Session|null Session storage object if available
   */
  protected $session = null;

  private $modules = array();

  /**
   * Module constructor
   * @param ModuleBase[] $modules An array of modules
   * @param Core $core The Core object 
   * @param mixed $var,... Additional arguments to relay to init() method
   */
  public final function __construct($modules, App $app) {
    $this->m = new Dictionary($modules, true);
    $this->Core = $app;
    $this->app = $app;

    if (isset($this->m->Http)) {
      $this->request = $this->m->Http->getRequest();
      $this->session = $this->request->session;
    }
    
    $additionalParameters = func_get_args();
    array_shift($additionalParameters);
    array_shift($additionalParameters);
    
    call_user_func_array(array($this, 'init'), $additionalParameters);
  }
  
  /**
   * Get the absolute path of a file
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path = '') {
    return $this->app->p($key, $path);
  }

  /**
   * Get the absolute path of a file relative to the public directory
   * @param string $path File
   * @return string Path
   */
  public function w($path = '') {
    return $this->app->w($path);
  }

  /**
   * Module initializer
   */
  protected abstract function init();
}
