<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

use Jivoo\Assets\Assets;
use Jivoo\Core\Utilities;

/**
 * Collection of scripts and stylesheets to be included into the template.
 */
class ViewResources {
  /**
   * @var array Associative array of providers.
   */
  private $providers = array();
  
  /**
   * @var string[] Script and style import blocks.
   */
  private $blocks = array(
    'script' => '',
    'style' => ''
  );

  /**
   * @var bool[] Associative array of emitted resources.
   */
  private $emitted = array();
  
  /**
   * @var string[][] Import frame stack. Each frame is an associative array
   * with keys 'script' and 'style', mapped to lists of script and style
   * imports. 
   */
  private $importFrames = array(
    0 => array(),
  );
  
  /**
   * @var int Index of current frame.
   */
  private $framePointer = 0;
  
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
   * @throws \Exception If unknown type.
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
        throw new \Exception(tr('Unknown type of resource: "%1"', $type));
    }
  }
  
  /**
   * Find a resource.
   * @param string $resource Resource name.
   * @throws \Exception If unknown type of resource.
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
        throw new \Exception(tr('Unknown type of resource: "%1"', $type));
    }
    if (!isset($location))
      $location = 'resource-is-missing/' . $resource;
//     if (!isset($location))
//       throw new \Exception(tr('Resource not found: "%1"', $resource));
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
    $this->providers[$resource] = $resInfo;
    $this->import($resource);
  }

  /**
   * Import a resource and its dependencies.
   * @param string $resource Resource name.
   * @param string $resources,... Additional resources to import.
   */
  public function import($resource) {
    if (is_array($resource)) {
      $args = $resource;
    }
    else {
      $args = func_get_args();
    }
    foreach ($args as $resource)
      $this->importFrames[$this->framePointer][] = $resource;
  }
  
  /**
   * Open a new resource frame on top of the current one.
   */
  public function openFrame() {
    $this->framePointer++;
    $this->importFrames[$this->framePointer] = array();
  }
  
  /**
   * Close the top resource frame.
   */
  public function closeFrame() {
    if ($this->framePointer < 1)
      return;
    $this->framePointer--;
    $frame = array_pop($this->importFrames);
    $this->importFrames[$this->framePointer] = array_merge(
      $frame,
      $this->importFrames[$this->framePointer]
    );
  }
  
  /**
   * Emit HTML for a resource.
   * @param string $resource Resource identifier.
   */
  private function emitResource($resource) {
    if ($this->emitted[$resource])
      return;
    $resInfo = $this->find($resource);
    if (!empty($resInfo['dependencies'])) {
      foreach ($resInfo['dependencies'] as $dependency)
        $this->emitResource($dependency);
    }
    $html = '';
      if (isset($resInfo['condition']))
        $html .= '<!--[if (' . $resInfo['condition'] . ')]>' . PHP_EOL;
    if ($resInfo['type'] == 'script') {
      $html .= '<script type="text/javascript" src="'
        . h($resInfo['location']) . '"></script>' . PHP_EOL;
    }
    else if ($resInfo['type'] == 'style') {
      $html .= '<link rel="stylesheet" type="text/css" href="'
        . h($resInfo['location']) . '" />' . PHP_EOL;
    }
    if (isset($resInfo['condition']))
      $html .= '<![endif]-->' . PHP_EOL;
    $this->blocks[$resInfo['type']] .= $html;
    $this->emitted[$resource] = true;
  }

  /**
   * Output resource block.
   * @return string Resource block HTML.
   */
  public function resourceBlock() {
    while ($this->framePointer > 0)
      $this->closeFrame();
    $this->emitted = array();
    $this->blocks = array(
      'script' => '',
      'style' => '',
    );
    foreach ($this->importFrames[$this->framePointer] as $resource)
      $this->emitResource($resource);
    $block = $this->blocks['style'] . PHP_EOL . $this->blocks['script'];
    return $block;
  }
}