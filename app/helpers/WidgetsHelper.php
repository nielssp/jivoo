<?php
/**
 * Widget helper
 * @package PeanutCMS\Widgets
 */
class WidgetsHelper extends Helper {
  
  protected $modules = array('Widgets');
  
  public function get($area) {
    return $this->m->Widgets->get($area);
  }
}