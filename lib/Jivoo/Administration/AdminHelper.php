<?php
class AdminHelper extends Helper {
  
  protected $modules = array('Assets', 'Templates');
  
  public function importDefaultTheme() {
    $this->m->Assets->addAssetDir('Administration', 'default/assets');
    $this->view->addTemplateDir($this->p('Administration', 'default/templates'));
  }
  
  public function component($name, $options = array()) {
    return '<pre>[[comp:' . $name . ']]</pre>';
  }
}