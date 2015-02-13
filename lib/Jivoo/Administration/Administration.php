<?php
// Module
// Name           : Jivoo Administration GUI Toolkit module
// Description    : Module for creating web application user interfaces
// Author         : apakoh.dk
// Dependencies   : Jivoo/View

Lib::import('Jivoo/Administration/Filtering');

/**
 * Administration module
 */
class Administration extends LoadableModule {
  
  protected $modules = array('View', 'Widgets');
  
  private $menu;
  
  protected function init() {
    $this->menu = new IconMenu(tr('Administration'));
  }
  
  public function __get($property) {
    switch ($property) {
      case 'menu':
        return $this->$property;
    }
    return parent::__get($property);
  }
}