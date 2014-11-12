<?php
class ExtensionInfo implements IBasicRecord {
  protected $kind = 'extensions';
  private $canonicalName;
  private $enabled;
  private $info;
  private $pKey;
  public function __construct($canonicalName, $info, $pKey = null, $enabled = true) {
    $this->canonicalName = $canonicalName;
    $this->info = $info;
    $this->pKey = $pKey;
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
  
  public function getData() {
    return $this->info;
  }
  
  public function getErrors() {
    return array();
  }
  
  public function isValid() {
    return true;
  }
  
  public function getModel() {
    return ExtensionModel::getInstance();
  }
  
  public function isBundled() {
    return $this->bundled;
  }
  
  public function p(App $app, $path) {
    if ($this->pKey)
      return $app->p($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      return $app->p($this->kind, $this->canonicalName . '/' . $path);
  }
  
  public function getAsset(Assets $assets, $path) {
    if ($this->pKey)
      return $assets->getAsset($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      return $assets->getAsset($this->kind, $this->canonicalName . '/' . $path);
  }
  
  public function addAssetDir(Assets $assets, $path) {
    if ($this->pKey)
      return $assets->addAssetDir($this->pKey, $this->kind . '/' . $this->canonicalName . '/' . $path);
    else
      return $assets->addAssetDir($this->kind, $this->canonicalName . '/' . $path);
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

  public function offsetExists($field) {
    return $this->__isset($field);
  }

  public function offsetGet($field) {
    return $this->__get($field);
  }

  public function offsetSet($field, $value) {
  }

  public function offsetUnset($field) {
  }
}