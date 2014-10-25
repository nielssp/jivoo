<?php
class ExtensionInfo {
  private $canonicalName;
  private $enabled;
  private $info;
  private $bundled;
  public function __construct($canonicalName, $info, $bundled = false, $enabled = true) {
    $this->canonicalName = $canonicalName;
    $this->info = $info;
    $this->bundled = $bundled;
    $this->enabled = $enabled;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'canonicalName':
      case 'enabled':
        return $this->$property;
    }
    return $this->info[$property];
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'canonicalName':
      case 'enabled':
        return true;
    }
    return isset($this->info[$property]);
  }
  
  public function isBundled() {
    return $this->bundled;
  }
  
  public function p(App $app, $path) {
    if ($this->bundled)
      return $app->p('app', 'extensions/' . $this->canonicalName . '/' . $path);
    else
      return $app->p('extensions', $this->canonicalName . '/' . $path);
  }
  
  public function getAsset(Assets $assets, $path) {
    if ($this->bundled)
      return $assets->getAsset('app', 'extensions/' . $this->canonicalName . '/' . $path);
    else
      return $assets->getAsset('extensions', $this->canonicalName . '/' . $path);
  }
  
  private function replaceVariable($matches) {
    if (isset($this->info[$matches[1]]))
      return $this->info[$matches[1]];
    return $matches[0];
  }
  
  public function replaceVariables($string) {
    return preg_replace_callback(
      '/\$([a-z0-9]+)/i',
      array($this, 'replaceVariable'),
      $string
    );
  }
}