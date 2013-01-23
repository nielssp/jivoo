<?php
// Module
// Name           : Assets
// Version        : 0.1.0
// Description    : The ApakohPHP asset system
// Author         : apakoh.dk
class Assets extends ModuleBase {
  
  private $docRoot = '';
  private $docRootLength = 0;
  
  protected function init() {
    $this->docRoot = $_SERVER['DOCUMENT_ROOT'];
    $this->docRootLength = strlen($this->docRoot);
  }
  
  public function getAsset($location) {
    if (strncmp($location, $this->docRoot, $this->docRootLength) == 0) {
      return substr($location, $this->docRootLength);
    }
    else {
      throw new Exception('ASSETS NOT IMPLEMENTED. PATH: ' . $location);
    }
  }
}