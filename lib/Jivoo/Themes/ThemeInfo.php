<?php
class ThemeInfo extends ExtensionInfo {

  protected $kind = 'themes';

  private $extend = array();
  private $zones = array();

  public function __construct($canonicalName, $info, $zones, $pKey = null) {
    parent::__construct($canonicalName, $info, $pKey, count($zones) > 0);
    if (isset($info['extend']) and is_array($info['extend']))
      $this->extend = $info['extend'];
    if (isset($info['zones']) and is_array($info['zones']))
      $this->zones = $info['zones'];
  }
  
  public function getModel() {
    return ThemeModel::getInstance();
  }
  
  public function __get($property) {
    switch ($property) {
      case 'extend':
      case 'zones':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'extend':
      case 'zones':
        return true;
    }
    return parent::__isset($property);
  }
}