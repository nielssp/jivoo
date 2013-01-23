<?php
// Module
// Name           : Assets
// Version        : 0.1.0
// Description    : The ApakohPHP asset system
// Author         : apakoh.dk
class Assets extends ModuleBase {
  
  private $docRoot = '';
  
  protected function init() {
    $this->docRoot = $_SERVER['DOCUMENT_ROOT'];
  }
  
  public function getAsset($location) {
    if (strncmp($location, $this->docRoot, strlen($this->docRoot)) == 0) {
      return substr($location, strlen($this->docRoot));
    }
    else {
      throw new Exception('ASSETS NOT IMPLEMENTED. PATH: ' . $location);
    }
  }
}