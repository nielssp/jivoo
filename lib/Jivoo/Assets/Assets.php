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
use Jivoo\Core\Logger;
use Jivoo\Core\ClassNotFoundException;
use Jivoo\View\TemplateNotFoundException;

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
   * @var array[] List of additional dynamic asset dirs
   */
  private $dynamicAssetDirs = array();
  
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
      'useCdnIfAvailable' => true,
      'mtimeSuffix' => true
    );
    
    $this->docRoot = Utilities::convertRealPath($_SERVER['DOCUMENT_ROOT']);
    $this->docRootLength = strlen($this->docRoot);
    
    $this->addAssetDir('Core', 'assets');

    if (isset($this->request->path[1]) and $this->request->path[0] == 'assets') {
      $path = $this->request->path;
      array_shift($path);
      if ($path == array('js', 'app.js'))
        $this->returnAppJs();
      if ($path == array('css', 'app.css'))
        $this->returnAppCss();
      $filename = explode('.', $path[(count($path) - 1)]);
      if (count($filename) > 1 and !empty($filename[0])) {
        $extension = strtolower(array_pop($filename));
        if (!in_array($extension, $this->extensionBlacklist)) {
          if (!$this->returnAsset($this->p('app', 'assets/' . implode('/', $path)))) {
            $key = $this->getPathKey(array_shift($path));
            $file = $this->p($key, implode('/', $path));
            if (!$this->returnAsset($file)) {
              $this->app->call('Routing', 'attachEventHandler', 'beforeFindRoute', array($this, 'findDynamicAsset'));
            }
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
   * Find a dynamic asset an return it to the client.
   */
  public function findDynamicAsset() {
    $path = $this->request->path;
    array_shift($path);
    if (!$this->returnDynamicAsset($this->p('app', 'assets/' . implode('/', $path)))) {
      $key = $this->getPathKey(array_shift($path));
      $file = $this->p($key, implode('/', $path));
      $this->returnDynamicAsset($file);
    }
  }

  /**
   * Find a dynamic asset an return it to the client.
   * @param string $path Path to asset
   * @return boolean False if file does not exist
   */
  private function returnDynamicAsset($path) {
    $route = $this->m->Routing->route;
    if (isset($route) and $route['priority'] >= 5)
      return;
//     if (isset($this->m->Snippets)) {
//       $name = str_replace('.', '_', implode('\\', array_map(array('Jivoo\Core\Utilities', 'dashesToCamelCase'), $path)));
//       try {
//         $snippet = $this->m->Snippets->getSnippet($name);
//         $response = $this->m->Routing->dispatch($snippet);
//         $response->cache();
//         $this->m->Routing->respond($response);
//       }
//       catch (ClassNotFoundException $e) { }
//     }
    try {
      $response = $this->m->Routing->dispatch(array($this->m->View, 'render'), $path . '.php');
      $response->cache();
      $this->m->Routing->respond($response);
    }
    catch (TemplateNotFoundException $e) { }
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
      if (file_exists($this->p('app', 'assets/' . $key)))
        return $this->getAssetUrl('app', $key);
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
    return $this->getAssetUrl($key, $path);
  }
  
  /**
   * Get an asset url.
   * @param string $key Path key.
   * @param string $path Path.
   * @return string Asset url.
   */
  private function getAssetUrl($key, $path) {
    $prefix = array('assets');
    if ($key == 'app') {
      $p = $this->p($key, 'assets/' . $path);
    }
    else {
      $prefix[] = $this->getAssetKey($key);
      $p = Utilities::convertRealPath($this->p($key, $path));
    }
    $suffix = '';
    if ($this->config['mtimeSuffix'])
      $suffix = '?' . filemtime($p);
    if (strncmp($p, $this->docRoot, $this->docRootLength) == 0)
      return substr($p, $this->docRootLength) . $suffix;
    else
      return $this->m->Routing->getLinkFromPath(
        array_merge($prefix, explode('/', $path))
      ) . $suffix;
  }
  
  /**
   * Get link to a dynamic asset.
   * @param string $key Path key.
   * @param string $path Path to template.
   * @return string|null Link to asset, or null if template not found.
   */
  public function getDynamicAsset($key, $path) {
    if ($key == 'app') {
      $p = $this->p($key, 'templates/assets/' . $path);
    }
    else {
      $prefix[] = $this->getAssetKey($key);
      $p = Utilities::convertRealPath($this->p($key, $path));
    }
    if (!isset($file))
      return null;
    $suffix = '';
    if ($this->config['mtimeSuffix'])
      $suffix = '?' . filemtime($file);
    return $this->m->Routing->getLinkFromPath(
      array_merge(array('assets'), explode('/', $template))
    ) . $suffix;
  }
}
