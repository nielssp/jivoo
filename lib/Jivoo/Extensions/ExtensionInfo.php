<?php
class ExtensionInfo {
  private $dir;
  private $info;
  public function __construct($dir, $info) {
    $this->dir = $dir;
    $this->info = $info;
  }
  
  public function __get($property) {
    if ($property == 'dir')
      return $this->dir;
    return $this->info[$property];
  }
  
  public function __isset($property) {
    return $property == 'dir' or isset($this->info[$property]);
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