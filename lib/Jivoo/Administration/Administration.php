<?php
// Module
// Name           : Jivoo Administration GUI Toolkit module
// Description    : Module for creating web application user interfaces
// Author         : apakoh.dk
// Dependencies   : Jivoo/Templates

/**
 * Administration module
 * @package Jivoo\Administration
 */
class Administration extends LoadableModule {
  
  protected $modules = array('Templates', 'Widgets');
  
  private $menu;
  
  protected function init() {
    $this->menu = new IconMenu(tr('Administration'));
  }
  
  public function __get($property) {
    switch ($property) {
      case 'menu':
        return $this->$property;
    }
  }
}