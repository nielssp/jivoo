<?php
class AdminHelper extends Helper {
  
  protected $modules = array('Assets', 'View', 'Administration');
  
  protected $helpers = array('Html', 'Widget');
  
  public function importDefaultTheme() {
    $this->m->Assets->addAssetDir('Administration', 'default/assets');
    $this->view->addTemplateDir($this->p('Administration', 'default/templates'));
  }
  
  public function menu($menu = 'main') {
    return $this->Widget->widget('IconMenu', array(
      'menu' => $this->m->Administration->menu[$menu],
    ));
  }
  
}