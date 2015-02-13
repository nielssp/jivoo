<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

use Jivoo\Assets\Assets;

/**
 * Collection of scripts and stylesheets to be included into the template.
 */
class ViewResources {
  /**
   * @var array Associative array of providers.
   */
  private $providers = array();
  
  /**
   * @var array Script and style imports.
   */
  private $imports = array(
    'script' => array(),
    'style' => array()
  );
  
  /**
   * @var bool[] Associative array of imported resources.
   */
  private $imported = array();
  
  /**
   * @var Assets Assets module.
   */
  private $assets;
  
  /**
   * Cnstruct collection of view resources.
   * @param Assets $assets Assets module.
   */
  public function __construct(Assets $assets) {
    $this->assets = $assets;
  }
  
  /**
   * Get a file.
   * @param string $file File name.
   * @return string|null Asset.
   */
  private function file($file) {
    return $this->assets->getAsset($file);
  }
  
  /**
   * Type of file extension.
   * @param string $resource Resource name.
   * @throws Exception If unknown type.
   * @return string Type.
   */
  private function type($resource) {
    $type = Utilities::getFileExtension($resource);
    switch ($type) {
      case 'js':
        return 'script';
      case 'css':
        return 'style';
      default:
        throw new Exception(tr('Unknown type of resource: "%1"', $type));
    }
  }
  
  /**
   * Find a resource.
   * @param string $resource Resource name.
   * @throws Exception If unknown type of resource.
   * @return array Description of resource and dependencies.
   */
  private function find($resource) {
    if (isset($this->providers[$resource]))
      return $this->providers[$resource];
    $type = Utilities::getFileExtension($resource);
    switch ($type) {
      case 'js':
        $type = 'script';
        $location = $this->file('js/' . $resource);
        break;
      case 'css':
        $type = 'style';
        $location = $this->file('css/' . $resource);
        break;
      default:
        throw new Exception(tr('Unknown type of resource: "%1"', $type));
    }
//     if (!isset($location))
//       throw new Exception(tr('Resource not found: "%1"', $resource));
    return array(
      'location' => $location,
      'type' => $type,
      'dependencies' => array(),
      'condition' => null
    );
  }
  
  /**
   * Provide a named resource.
   * @param string $resource Resource name, e.g. 'jquery.js'.
   * @param string $location Location of resource (relative to docroot).
   * @param string[] $dependencies List resource dependencies.
   * @param string $condition Condition for resource.
   */
  public function provide($resource, $location, $dependencies = array(), $condition = null) {
    $this->providers[$resource] = array(
      'location' => $location,
      'type' => $this->type($resource),
      'dependencies' => $dependencies,
      'condition' => $condition
    );
  }
  
  /**
   * Import conditional resource.
   * @param string $resource Resource name.
   * @param string $condition Condition.
   */
  public function importConditional($resource, $condition) {
    if (isset($this->imported[$resource]))
      return;
    $resInfo = $this->find($resource);
    $resInfo['condition'] = $condition;
    if (empty($resInfo['dependencies'])) {
      array_unshift($this->imports[$resInfo['type']], $resInfo);
    }
    else {
      foreach ($resInfo['dependencies'] as $dependency)
        $this->import($dependency);
      array_push($this->imports[$resInfo['type']], $resInfo);
    }
    $this->imported[$resource] = true;
  }
  
  /**
   * Push resource on import stack.
   * @param string $resource Resource name.
   */
  private function push($resource) {
    if (isset($this->imported[$resource]))
      return;
    $resInfo = $this->find($resource);
    foreach ($resInfo['dependencies'] as $dependency)
      $this->unshift($dependency);
    array_push($this->imports[$resInfo['type']], $resInfo);
    $this->imported[$resource] = true;
  }
  
  /**
   * Unshift a resource to import queue.
   * @param string $resource Resource name.
   */
  private function unshift($resource) {
    if (isset($this->imported[$resource]))
      return;
    $resInfo = $this->find($resource);
    if (empty($resInfo['dependencies'])) {
      array_unshift($this->imports[$resInfo['type']], $resInfo); 
    }
    else {
      foreach ($resInfo['dependencies'] as $dependency)
        $this->unshift($dependency);
      array_push($this->imports[$resInfo['type']], $resInfo);
    }
    $this->imported[$resource] = true;
  }
  
  /**
   * Import a resource and its dependencies.
   * @param string $resource Resource name.
   */
  public function import($resource) {
    if (is_array($resource)) {
      if (count($resource) == 0) {
        return;
      }
      else if (count($resource) == 1) {
        $resource = $resource[0];
      }
      else {
        $dependencies = $resource;
        $resource = array_pop($dependencies);
        $this->import($dependencies);
        return $this->push($resource);
      }
    }
    else {
      $args = func_get_args();
      if (count($args) > 1)
        return $this->import($args);
    }
    return $this->unshift($resource);
  }
  
  /**
   * Output resource block.
   * @return string Resource block HTML.
   */
  public function resourceBlock() {
    $block = '';
    foreach ($this->imports['style'] as $resource) {
      if (isset($resource['condition']))
        $block .= '<!--[if (' . $resource['condition'] . ')]>' . PHP_EOL;
      $block .= '<link rel="stylesheet" type="text/css" href="'
        . h($resource['location']) . '" />' . PHP_EOL;
      if (isset($resource['condition']))
        $block .= '<![endif]-->' . PHP_EOL;
    }
    $block .= PHP_EOL;
    foreach ($this->imports['script'] as $resource) {
      if (isset($resource['condition']))
        $block .= '<!--[if (' . $resource['condition'] . ')]>' . PHP_EOL;
      $block .= '<script type="text/javascript" src="'
        . h($resource['location']) . '"></script>' . PHP_EOL;
      if (isset($resource['condition']))
        $block .= '<![endif]-->' . PHP_EOL;
    }
    return $block;
  }
}