<?php
class ConfigSetupController extends SetupController {
  
  protected $helpers = array('Html', 'Form');
  
  public function install() {
    $this->title = tr('Welcome to Jivoo ADK');
    if ($this->request->hasValidData()) {
      return $this->Setup->done();
    }
    return $this->render();
  }
}