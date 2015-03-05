<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

use Jivoo\Routing\ILinkable;

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
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'label':
      case 'icon':
      case 'route':
      case 'badge':
        return isset($this->$property);
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  public function getRoute() {
    return $this->route;
  }
}