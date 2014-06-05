<?php
class IconMenuItem implements ILinkable {
  
  private $label;
  private $icon;
  private $route;
  private $badge;
  
  public function __construct($label, $route = array(), $icon = null, $badge = null) {
    $this->label = $label;
    $this->route = $route;
    $this->icon = $icon;
    $this->badge = $badge;
  }
  

  public function __get($property) {
    switch ($property) {
      case 'label':
      case 'icon':
      case 'route':
      case 'badge':
        return $this->$property;
    }
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'label':
      case 'icon':
      case 'route':
      case 'badge':
        return isset($this->$property);
    }
  }
  
  public function getRoute() {
    return $this->route;
  }
}