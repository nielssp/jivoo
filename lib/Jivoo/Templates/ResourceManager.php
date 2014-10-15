<?php
class ResourceManager extends Module {
  
  protected $modules = array('Assets');
  
  private $providers = array();
  
  private $imports = array(
    'script' => array(),
    'style' => array()
  );
  
  private function file($file) {
    return $this->m->Assets->getAsset($file);
  }
  
  private function type($resource) {
    $type = Utilitites::getFileExtension($resource);
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
    $type = Utilitites::getFileExtension($resource);
    switch ($type) {
      case 'js':
        $type = 'script';
        $location = $this->file('js/' . $resource);
        break;
      case 'css':
        $type = 'style';
        $location = $this->file('css/' . $file);
        break;
      default:
        throw new Exception(tr('Unknown type of resource: "%1"', $type));
    }
    return array(
      'location' => $location,
      'type' => $type,
      'dependencies' => array(),
      'condition' => $condition
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
  
  public function import($resource) {
    if (isset($this->imports[$resource]))
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
    foreach ($this->imports['script'] as $resource) {
      if (isset($resource['condition']))
        $block .= '<!--[if (' . $resource['condition'] . ')]>' . PHP_EOL;
      $block .= '<script type="text/javascript" src="'
        . h($resource['location']) . '"</script>>' . PHP_EOL;
      if (isset($resource['condition']))
        $block .= '<![endif]-->' . PHP_EOL;
    }
    return $block;
  }
}