<?php
// Module
// Name           : Assets
// Version        : 0.1.0
// Description    : The ApakohPHP asset system
// Author         : apakoh.dk
// Dependencies   : ApakohPHP/Http
class Assets extends ModuleBase {
  
  private $docRoot = '';
  private $docRootLength = 0;
  
  private $blacklist = array(
    'php'
  );
  
  protected function init() {
    $this->docRoot = $_SERVER['DOCUMENT_ROOT'];
    $this->docRootLength = strlen($this->docRoot);
    
    if ($this->request->path[0] == 'assets' AND isset($this->request->path[1])) {
      $path = $this->request->path;
      array_shift($path);
      if (count($path) == 1) {
        if ($this->returnAsset($this->p('assets', 'css/' . $path[0]))) {
          exit;
        }
        else if ($this->returnAsset($this->p('assets', 'js/' . $path[0]))) {
          exit;
        }
        else if ($this->returnAsset($this->p('assets', 'img/' . $path[0]))) {
          exit;
        }
      }
      $key = array_shift($path);
      $filename = explode('.', $path[(count($path)-1)]);
      if (count($filename) > 1 AND !empty($filename[0])) {
        $extension = strtolower(array_pop($filename));
        if (!in_array($extension, $this->blacklist)) {
          switch ($extension) {
            case 'css':
          }
          $file = $this->p($key, implode('/', $path));
          if ($this->returnAsset($file)) {
            exit;
          }
        }
      }
    }
  }
  
  private function returnAsset($path) {
    if (file_exists($path)) {
      header('Content-Type: ' . Utilities::getContentType($path));
      echo file_get_contents($path);
      return true;
    }
    return false;
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