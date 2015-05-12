<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

/**
 * A JTK object contains information and logic used when rendering JTK snippets.
 * All JTK objects can be extended with custom properties
 * using the magic getters and setters.
 */
class JtkObject {
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
  
  /**
   * Call a property setter for chaining (of the form "setProperty(value)"
   * where "property" is the property).
   * @param string $method Method name.
   * @param mixed[] $parameters Parameters.
   * @throws \InvalidMethodException If method undefined. 
   * @return self Self.
   */
  public function __call($method, $parameters) {
    if (isset($parameters[0]) and preg_match('/^set([A-Z].*)$/', $method, $matches)) {
      $property = $matches[1];
      $this->properties[$property] = $parameters[0];
      return $this;
    }
    throw new \InvalidMethodException(tr('Invalid method: %1', $method));
  }
  
}