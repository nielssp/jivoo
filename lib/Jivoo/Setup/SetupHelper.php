<?php
class SetupHelper extends Helper {
  protected $modules = array('Setup');
  
  public function __get($property) {
    if ($property == 'done') {
      return $this->m->Setup->currentState;
    } 
    return parent::__get($property); 
  }
  
  public function __set($property, $value) {
    if ($property == 'done') {
      $this->m->Setup->currentState = $value;
    }
  }
  
  public function done() {
    $this->done = true;
    return $this->m->Routing->redirect(null);
  }
  
  public function setState($route, $done) {
    $this->m->Setup->setState($route, $done);
    return $this->m->Routing->redirect(null);
  }
  
  public function getState($route) {
    return $this->m->Setup->getState($route);
  }
}