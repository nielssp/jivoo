<?php
/**
 * Widget helper
 * @package Jivoo\Widgets
 */
class WidgetsHelper extends Helper {
  
  protected $modules = array('Widgets');
  
  public function get($area) {
    return $this->m->Widgets->get($area);
  }
}