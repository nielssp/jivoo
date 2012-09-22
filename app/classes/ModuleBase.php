<?php
/**
 * Base class for modules
 * @package PeanutCMS
 */
abstract class ModuleBase {
  
  /**
   * @var Core The Core object
   */
  protected $Core = null;

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
  public final function __construct($modules, Core $core) {
    $this->m = new Dictionary($modules, true);
    $this->Core = $core;

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
   * Module initializer
   */
  protected abstract function init();
}
