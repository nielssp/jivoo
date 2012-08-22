<?php

abstract class ModuleBase {
  
  protected $Core = null;
  protected $m = null;

  protected $request = null;
  protected $session = null;

  private $modules = array();
  
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
  
  protected abstract function init();
}
