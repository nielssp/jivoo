<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * A class that statically defines one or more macros. All macros are static
 * functions in all lowercase prefixed with an underscore. 
 */
abstract class Macros {
  /**
   * @var array Macros.
   */
  private static $macros = array();

  private final function __construct() { }
  
  /**
   * Get an associative array of macro names and functions.
   * @param string $class Class name of Macros-class.
   * @return callable[] Associative array mapping macro names to callables.
   */
  public static function getMacros($class = null) {
    if (!isset($class))
      $class = get_called_class();

    if (!isset(self::$macros[$class])) {
      Lib::assumeSubclassOf($class, 'Jivoo\View\Compile\Macros');
      $ref = new \ReflectionClass($class);
      $methods = $ref->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC);
      $macros = array();
      foreach ($methods as $method) {
        if (preg_match('/^_([a-zA-Z]+)$/', $method->getName(), $matches) === 1) {
          $macros[strtolower($matches[1])] = array($class, $matches[0]);
        }
      }
      self::$macros[$class] = $macros;
    }
    return self::$macros[$class];
  }
}