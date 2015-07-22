<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * A class that implements one or more macros. All macros are public methods in
 * all lowercase prefixed with an underscore. 
 */
abstract class Macros {
  /**
   * @var callable[] Macros.
   */
  private $macros = null;
  
  /**
   * @var string Macro namespace.
   */
  protected $namespace = 'j';
  
  /**
   * @var string[] List of automatic properties.
   */
  protected $properties = array();

  /**
   * Get an associative array of macro names and functions.
   * @param string $class Class name of Macros-class.
   * @return callable[] Associative array mapping macro names to callables.
   */
  public function getMacros() {
    if (!isset($this->macros)) {
      $ref = new \ReflectionClass($this);
      $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
      $macros = array();
      foreach ($methods as $method) {
        if (preg_match('/^_([a-zA-Z]+)$/', $method->getName(), $matches) === 1) {
          $macros[strtolower($matches[1])] = array($this, $matches[0]);
        }
      }
      $namespace = $this->namespace;
      foreach ($this->properties as $property) {
        $property = strtolower($property); 
        $macros[$property] = function(HtmlNode $node, $value) use($namespace, $property) {
          $node->setProperty($namespace . ':' . $property, $value);
        }; 
      }
      $this->macros = $macros;
    }
    return $this->macros;
  }
  
  /**
   * Get macro namespace.
   * @return string Macro namespace.
   */
  public function getNamespace() {
    return $this->namespace;
  }
}