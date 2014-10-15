<?php
class ResourceManager {
  
  private $providers = array();
  
  private $imports = array(
    'script' => array(),
    'style' => array()
  );
  
  private $imported = array();
  
  private $assets;
  
  public function __construct(Assets $assets) {
    $this->assets = $assets;
  }
  
  private function file($file) {
    return $this->assets->getAsset($file);
  }
  
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
  
  public function provide($resource, $location, $dependencies = array(), $condition = null) {
    $this->providers[$resource] = array(
      'location' => $location,
      'type' => $this->type($resource),
      'dependencies' => $dependencies,
      'condition' => $condition
    );
  }
  
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
  
  public function import($resource) {
    if (is_array($resource)) {
      $resources = array_reverse($resource);
      foreach ($resources as $resource)
        $this->import($resource);
      return;
    }
    $args = func_get_args();
    if (count($args) > 1)
      return $this->import($args);
    if (isset($this->imported[$resource]))
      return;
    $resInfo = $this->find($resource);
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
  
  public function createBlock() {
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