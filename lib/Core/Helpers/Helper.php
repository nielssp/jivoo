<?php

abstract class Helper implements IHelpable {

  protected $m = null;
  protected $request = null;
  protected $session = null;
  protected $auth = null;

  protected $modules = array();
  protected $helpers = array();

  private $helperObjects = array();

  public final function __construct(Routing $routing) {
    $this->m = new Dictionary();
    
    $this->m->Routing = $routing;

    $this->request = $routing->getRequest();
    $this->session = $this->request->session;

    $this->init();
  }

  public function __get($name) {
    if (isset($this->helperObjects[$name])) {
      return $this->helperObjects[$name];
    }
  }

  protected function init() {}

  
  public function getModuleList() {
    return $this->modules;
  }
  
  public function getHelperList() {
    return $this->helpers;
  }

  public function addModule($object) {
    $class = get_class($object);
    if ($object instanceof Authentication) {
      $this->auth = $object;
    }
    $this->m->$class = $object;
  }
  
  public function addHelper($helper) {
    $name = str_replace('Helper', '', get_class($object));
    $this->helperObjects[$name] = $helper;
  }
  

  protected function getLink($route) {
    return $this->m->Routing->getLink($route);
  }

}
