<?php
// Module
// Name           : Helpers
// Version        : 0.3.14
// Description    : For helpers
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates

/**
 * Helpers module
 * 
 * @package Core
 * @subpackage Helpers
 */
class Helpers extends ModuleBase {
  
  private $helpers = array();
  
  protected function init() {
    $dir = opendir($this->p('helpers', ''));
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
        $class = $split[0];
        $name = str_replace('Helper', '', $class);
        $this->helpers[$name] = new $class($this->m->Routing, $this);
      }
    }
  }
  
  public function getHelpers($helpers) {
    $helperObjects = array();
    foreach ($helpers as $name) {
      if (isset($this->helpers[$name])) {
        $helperObjects[$name] = $this->helpers[$name];
      }
      else {
        $class = $name . 'Helper';
        if (class_exists($class)) {
          $this->helperObjects[$name] = new $class($this->m->Routing, $this);
        }
        else {
          Logger::warning('Invalid helper: ' . $name);
        }
      }
    }
    return $helperObjects;
  }
}