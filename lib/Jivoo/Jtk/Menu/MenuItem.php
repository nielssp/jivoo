<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

/**
 * A menu item.
 * @property string $label Item label.
 */
abstract class MenuItem {  
  public function __get($property) {
    if (isset($this->properties[$property]))
      return $this->properties[$property];
    return null;
  }
  
  public function __set($property, $value) {
    $this->properties[$property] = $value;
  }
  
  public function __isset($property) {
    return isset($this->properties[$property]);
  }
  
  public function __unset($property) {
    if (isset($this->properties[$property]))
      unset($this->properties[$property]);
  }
  
}