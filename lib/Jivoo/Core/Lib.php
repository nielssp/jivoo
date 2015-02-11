<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Library system for importing directories and autoloading classes.
 */
class Lib {
  /**
   * @var array List of paths (strings) to look for classes
   */
  private static $paths = array();
  
  /**
   * @var bool Whether or not to throw exceptions
   */
  public static $throwExceptions = true;

  private function __construct() {}

  /**
   * Add another library path.
   * @param string $path Path.
   */
  public static function import($path) {
    self::$paths[] = $path;
  }

  /**
   * Check if a class exists anywhere
   * @param string $className Name of class
   * @param boolean $autoload Whether or not to autoload it if it does
   * @return boolean True if it exists, false otherwise
   */
  public static function classExists($className, $autoload = true) {
    if (!$autoload) {
      return class_exists($className, false);
    }
    if (self::$throwExceptions) {
      self::$throwExceptions = false;
      $result = class_exists($className, true);
      self::$throwExceptions = true;
      return $result;
    }
    return class_exists($className, true);
  }
  
  /**
   * Check whether or not $class extends $parent, and throw an exception if
   * it does not
   * @param string $class Class name
   * @param string $parent Expected parent class of $class
   * @throws ClassInvalidException if $class does not extend $parent
   * @throws ClassNotFoundException if $class does not exist
   */
  public static function assumeSubclassOf($class, $parent) {
    if (!is_subclass_of($class, $parent)) {
      throw new ClassInvalidException(tr(
        'Class "%1" should extend "%2"', $class, $parent
      ));
    }
  } 
  
  /**
   * Auto loader
   * @param string $className Name of class
   * @throws ClassNotFoundException if class not found (and Lib::$throwExceptions
   * is true)
   * @return boolean True on success false on failure
   */
  public static function autoload($className) {
    $className = str_replace('\\', '/', $className);
    foreach (self::$paths as $path) {
      $classPath = $path . '/' . $className . '.php';
      if (file_exists($classPath)) {
        require $classPath;
        return true;
      }
    }
    if (self::$throwExceptions) {
      throw new ClassNotFoundException(tr('Class not found: "%1"', $className));
    }
    return false;
  }
}

/**
 * Thrown when a class could not be found
 */
class ClassNotFoundException extends \Exception { }

/**
 * Thrown when a class is invalid
 */
class ClassInvalidException extends \Exception { }
