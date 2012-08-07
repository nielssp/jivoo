<?php

abstract class ModuleBase {
  
  protected $Core = NULL;
  protected $m = NULL;

  protected $request = NULL;

  private $modules = array();
  
  public final function __construct($modules, Core $core) {
    $this->m = new Dictionary($modules, TRUE);
    $this->Core = $core;

    if (isset($this->m->Http)) {
      $this->request = $this->m->Http->getRequest();
    }
    
    $additionalParameters = func_get_args();
    array_shift($additionalParameters);
    array_shift($additionalParameters);
    
    call_user_func_array(array($this, 'init'), $additionalParameters);
  }
  
  protected abstract function init();
}
