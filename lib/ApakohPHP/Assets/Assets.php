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

  private $extensionBlacklist = array('php');

  private $pathWhitelist = array();

  protected function init() {
    $this->docRoot = $_SERVER['DOCUMENT_ROOT'];
    $this->docRootLength = strlen($this->docRoot);

    if ($this->request
      ->path[0] == 'assets' AND isset($this->request
          ->path[1])) {
      $path = $this->request
        ->path;
      array_shift($path);
      $filename = explode('.', $path[(count($path) - 1)]);
      if (count($filename) > 1 AND !empty($filename[0])) {
        $extension = strtolower(array_pop($filename));
        if (!in_array($extension, $this->extensionBlacklist)) {
          if ($this->returnAsset($this->p('assets', implode('/', $path)))) {
            exit;
          }
          else {
            $key = array_shift($path);
            $file = $this->p($key, implode('/', $path));
            if ($this->returnAsset($file)) {
              exit;
            }
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

  public function getAsset($key, $path = null) {
    if (!isset($path)) {
      $path = $key;
      $key = 'assets';
    }
    $p = $this->p($key, $path);
    if (!strncmp($p, $this->docRoot, $this->docRootLength) == 0) {
      return substr($p, $this->docRootLength);
    }
    else {
      $pArray = ($key == 'assets' ? array($key) : array('assets', $key));
      $pArray = array_merge($pArray, explode('/', $path));
      return $this->m
        ->Http
        ->getLink($pArray);
    }
  }
}
