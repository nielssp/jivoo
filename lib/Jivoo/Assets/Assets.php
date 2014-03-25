<?php
// Module
// Name           : Assets
// Description    : The Jivoo asset system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Routing

/**
 * Assets module
 * 
 * @package Jivoo\Assets
 */
class Assets extends ModuleBase {
  /**
   * @var string Document root
   */
  private $docRoot = '';
  
  /**
   * @var int Length of document root
   */
  private $docRootLength = 0;

  /**
   * @var string[] List of blacklisted extensions
   */
  private $extensionBlacklist = array('php', 'log');
  
  /**
   * @var array[] List of additional asset dirs
   */
  private $assetDirs = array();
  
  /**
   * @var bool Whether or not $assetDirs is sorted
   */
  private $sorted = true;

  protected function init() {
    $this->docRoot = $_SERVER['DOCUMENT_ROOT'];
    $this->docRootLength = strlen($this->docRoot);

    if (isset($this->request->path[1]) AND $this->request->path[0] == 'assets') {
      $path = $this->request->path;
      array_shift($path);
      $filename = explode('.', $path[(count($path) - 1)]);
      if (count($filename) > 1 AND !empty($filename[0])) {
        $extension = strtolower(array_pop($filename));
        if (!in_array($extension, $this->extensionBlacklist)) {
          if (!$this->returnAsset($this->p('assets', implode('/', $path)))) {
            $key = array_shift($path);
            $file = $this->p($key, implode('/', $path));
            $this->returnAsset($file);
          }
        }
      }
    }
  }

  private function minifyJs($js) {
    // TODO add javascript minification
    return $js;
  }

  private function minifyCss($css) {
    // TODO add CSS minification
    return $css;
  }

  private function returnAppJs() {
    $text = '';
    $dir = opendir($this->p('assets', 'js'));
    while ($file = readdir($dir)) {
      $path = $this->p('assets', 'js/' . $file);
      if (is_file($path)) {
        $text .= $this->minifyJs(file_get_contents($path)) . PHP_EOL;
      }
    }
    $response = new TextResponse(Http::OK, 'application/javascript', $text);
    $response->cache();
    $this->m->Routing->respond($response);
  }

  private function returnAppCss() {
    $text = '';
    $dir = opendir($this->p('assets', 'css'));
    while ($file = readdir($dir)) {
      $path = $this->p('assets', 'css/' . $file);
      if (is_file($path)) {
        $text .= $this->minifyCss(file_get_contents($path)) . PHP_EOL;
      }
    }
    $response = new TextResponse(Http::OK, 'text/css', $text);
    $response->cache();
    $this->m->Routing->respond($response);
  }

  /**
   * Find an asset an return it to the client
   * @param string $path Path to asset
   * @return boolean False if file does not exist
   */
  private function returnAsset($path) {
    if (file_exists($path)) {
      $response = new AssetResponse($path);
      $response->cache();
      $this->m->Routing->respond($response);
    }
    return false;
  }
  
  /**
   * Add additional asset dirs
   * @param string $key Location-identifier
   * @param string $path Dir path
   * @param int $priority Priority
   */
  public function addAssetDir($key, $path, $priority = 5) {
    $this->sorted = false;
    $this->assetDirs[] = array(
      'key' => $key,
      'path' => $path,
      'priority' => $priority
    );
  }

  /**
   * Get a link to an asset
   * @param string $key Path key or path if second parameter undefined
   * @param string $path Path to file, if undefined first parameter is used and
   * key is set to 'assets'
   * @return string A link to the asset
   */
  public function getAsset($key, $path = null) {
    if (!isset($path)) {
      $path = $key;
      $key = 'assets';
      if (!$this->sorted) {
        uasort($this->assetDirs, array('Utilities', 'prioritySorter'));
        $this->sorted = true;
      }
      foreach ($this->assetDirs as $dir) {
        if (file_exists($this->p($dir['key'], $dir['path'] . '/' . $path))) {
          $key = $dir['key'];
          $path = $dir['path'] . '/' . $path;
        }
      }
    }
    $p = $this->p($key, $path);
    if (strncmp($p, $this->docRoot, $this->docRootLength) == 0) {
      return substr($p, $this->docRootLength);
    }
    else {
      $pArray = ($key == 'assets' ? array($key) : array('assets', $key));
      $pArray = array_merge($pArray, explode('/', $path));
      return $this->m
        ->Routing
        ->getLinkFromPath($pArray);
    }
  }
}
