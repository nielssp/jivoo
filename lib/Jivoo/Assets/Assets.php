<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Assets;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Utilities;
use Jivoo\Routing\TextResponse;
use Jivoo\Routing\Http;
use Jivoo\Extensions\ExtensionInfo;

/**
 * Asset system.
 */
class Assets extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing');
  
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

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->config->defaults = array(
      'minifyJs' => true,
      'minifyCss' => true,
      'compileScss' => false,
      'useCdnIfAvailable' => true
    );
    
    $this->docRoot = Utilities::convertRealPath($_SERVER['DOCUMENT_ROOT']);
    $this->docRootLength = strlen($this->docRoot);
    
    $this->addAssetDir('Core', 'assets');

    if (isset($this->request->path[1]) AND $this->request->path[0] == 'assets') {
      $path = $this->request->path;
      array_shift($path);
      if ($path == array('js', 'app.js'))
        $this->returnAppJs();
      if ($path == array('css', 'app.css'))
        $this->returnAppCss();
      $filename = explode('.', $path[(count($path) - 1)]);
      if (count($filename) > 1 AND !empty($filename[0])) {
        $extension = strtolower(array_pop($filename));
        if (!in_array($extension, $this->extensionBlacklist)) {
          if (!$this->returnAsset($this->p('app', 'assets/' . implode('/', $path)))) {
            $key = $this->getPathKey(array_shift($path));
            $file = $this->p($key, implode('/', $path));
            $this->returnAsset($file);
          }
        }
      }
    }
    
    // lazy call to Extensions module
    $this->app->call('Extensions', 'attachFeature', 'resources', array($this, 'handleResources'));
  }
  
  /**
   * Handle "resources" extension feature.
   * @param ExtensionInfo $info Extension information.
   */
  public function handleResources(ExtensionInfo $info) {
    foreach ($info->resources as $resource => $resInfo) {
      $dependencies = isset($resInfo['dependencies']) ? $resInfo['dependencies'] : array();
      $condition = isset($resInfo['condition']) ? $resInfo['condition'] : null;
      if (isset($resInfo['cdn']) and $this->config['useCdnIfAvailable'])
        $file = $info->replaceVariables($resInfo['cdn']);
      else
        $file = $info->getAsset($this, $info->replaceVariables($resInfo['file']));
      $this->m->View->resources->provide(
        $resource,
        $file,
        $dependencies,
        $condition
      );
    }
  }

  private function minifyJs($js) {
    // TODO add javascript minification + caching
    return $js;
  }

  private function minifyCss($css) {
    // TODO add CSS minification + caching
    return $css;
  }

  /**
   * Respond with all JavaScript assets in one file.
   */
  private function returnAppJs() {
    $text = '';
    $files = scandir($this->p('app', 'assets/js'));
    if ($files !== false) {
      foreach ($files as $file) {
        $path = $this->p('app', 'assets/js/' . $file);
        if (is_file($path)) {
          $text .= '//' . $file . PHP_EOL;
          $text .= $this->minifyJs(file_get_contents($path)) . PHP_EOL;
        }
      }
    }
    $response = new TextResponse(Http::OK, 'application/javascript', $text);
    $response->cache();
    $this->m->Routing->respond($response);
  }

  /**
   * Respond with all CSS assets in one file.
   */
  private function returnAppCss() {
    $text = '';
    $files = scandir($this->p('app', 'assets/css'));
    if ($files !== false) {
      foreach ($files as $file) {
        $path = $this->p('app', 'assets/css/' . $file);
        if (is_file($path)) {
          $text .= $this->minifyCss(file_get_contents($path)) . PHP_EOL;
        }
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
   * Convert a path key to an asset key (converts backslashes to dashes).
   * @param string $key Path key.
   * @return string Asset key.
   */
  public function getAssetKey($key) {
    return str_replace('\\', '-', $key);
  }
  
  /**
   * Convert an asset key to a path key (converts dashes to backslashes).
   * @param string $key Asset key.
   * @return string Path key.
   */
  public function getPathKey($key) {
    return str_replace('-', '\\', $key);
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
      if (file_exists($this->p('app', 'assets/' . $key))) {
        $p = $this->p('app', 'assets/' . $key);
        if (strncmp($p, $this->docRoot, $this->docRootLength) == 0)
          return substr($p, $this->docRootLength);
        else
          return $this->m->Routing->getLinkFromPath(
            array_merge(array('assets'), explode('/', $key))
          );
      }
      else {
        if (!$this->sorted) {
          uasort($this->assetDirs, array('Jivoo\Core\Utilities', 'prioritySorter'));
          $this->sorted = true;
        }
        $path = $key;
        $key = null;
          foreach ($this->assetDirs as $dir) {
          if (file_exists($this->p($dir['key'], $dir['path'] . '/' . $path))) {
            $key = $dir['key'];
            $path = $dir['path'] . '/' . $path;
          }
        }
        if (!isset($key))
          return null;
      }
    }
    $p = Utilities::convertRealPath($this->p($key, $path));
    if (strncmp($p, $this->docRoot, $this->docRootLength) == 0)
      return substr($p, $this->docRootLength);
    else
      return $this->m->Routing->getLinkFromPath(
        array_merge(array('assets', $this->getAssetKey($key)), explode('/', $path))
      );
  }
}
