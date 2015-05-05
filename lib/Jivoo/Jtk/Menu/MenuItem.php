<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

/**
 * A menu item. All menu items can be extended with custom properties, e.g.
 * label, icon, shortcut key, etc., using the magic getters and setters.
 */
abstract class MenuItem {
  /**
   * @var array Properties.
   */
  private $properties = array();
  
  /**
   * Get value of property.
   * @param string $property Property name.
   * @return mixed Value or null if undefined.
   */
  public function __get($property) {
    if (isset($this->properties[$property]))
      return $this->properties[$property];
    return null;
  }

  /**
   * Set value of property.
   * @param string $property Property name.
   * @param mixed $value Value.
   */
  public function __set($property, $value) {
    $this->properties[$property] = $value;
  }

  /**
   * Whether or not a property exists and has a value.
   * @param string $property Property name.
   * @return bool True if property exists and isn't null.
   */  
  public function __isset($property) {
    return isset($this->properties[$property]);
  }
  
  /**
   * Delete a property.
   * @param string $property Property name.
   */
  public function __unset($property) {
    if (isset($this->properties[$property]))
      unset($this->properties[$property]);
  }
  
}