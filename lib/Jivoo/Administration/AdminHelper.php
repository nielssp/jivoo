<?php
class AdminHelper extends Helper {
  
  protected $modules = array('Assets', 'View', 'Administration');
  
  protected $helpers = array('Html', 'Widget');
  
  public function menu($menu = 'main') {
    return $this->Widget->widget('IconMenu', array(
      'menu' => $this->m->Administration->menu[$menu],
    ));
  }
  
}