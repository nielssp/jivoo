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
  
  protected $modules = array('Templates');
  
  private $menu;
  
  protected function init() {
    // read menu-config or something? 
  }
  
  public function __get($property) {
    switch ($property) {
      case 'menu':
        return $this->$property;
    }
  }
}