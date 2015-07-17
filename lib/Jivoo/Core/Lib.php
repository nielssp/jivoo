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
   * @var array List of paths to look for classes in.
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
   * @param string $namespace Root namespace of library.
   */
  public static function import($path, $namespace = '') {
    $namespace = str_replace('\\', '/', $namespace);
    self::$paths[] = array(
      'root' => $path,
      'namespace' => $namespace,
      'namespacelen' => strlen($namespace)
    );
  }
  
  /**
   * Get namespace part of a class name.
   * @param string|object $className Class or object, e.g. 'Jivoo\Core\Lib'.
   * @return string Namespace, e.g. 'Jivoo\Core'.
   */
  public static function getNamespace($className) {
    if (is_object($className))
      $className = get_class($className);
    if (strpos($className, '\\') === false)
      return '';
    return preg_replace('/\\\\[^\\\\]+$/', '', $className);
  }
  
  /**
   * Get class name part of a qualified class name.
   * @param string|object $className Class or object, e.g. 'Jivoo\Core\Lib'.
   * @return string Class name, e.g. 'Lib'.
   */
  public static function getClassName($className) {
    if (is_object($className))
      $className = get_class($className);
    $className = array_slice(explode('\\', $className), -1);
    return $className[0];
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
   * @param string|object $class Class name or object.
   * @param string $parent Expected parent class of $class
   * @throws ClassInvalidException if $class does not extend $parent
   * @throws ClassNotFoundException if $class does not exist
   */
  public static function assumeSubclassOf($class, $parent) {
    if (!is_subclass_of($class, $parent)) {
      if (is_object($class))
        $class = get_class($class);
      if ($class === $parent)
        return;
      throw new ClassInvalidException(tr(
        'Class "%1" should extend "%2"', $class, $parent
      ));
    }
  } 
  
  /**
   * Attempt to load a file from the known library paths.
   * @param string $file File name.
   * @return boolean True if loaded, false otherwise.
   */
  private static function loadFile($file) {
    foreach (self::$paths as $path) {
      if ($path['namespace'] != '') {
        if (substr_compare($file, $path['namespace'], 0, $path['namespacelen']) !== 0)
          continue;
        $classPath = $path['root'] . '/' . substr($file, $path['namespacelen'] + 1);
      }
      else {
        $classPath = $path['root'] . '/' . $file;
      }
      if (file_exists($classPath)) {
        require $classPath;
        return true;
      }
    }
    return false;
  }
  
  /**
   * Auto loader
   * @param string $className Name of class
   * @throws ClassNotFoundException if class not found (and {@see $throwExceptions}
   * is true)
   * @return boolean True on success false on failure
   */
  public static function autoload($className) {
    if (substr_compare($className, 'Exception', -9) === 0) {
      $fileName = str_replace('\\', '/', self::getNamespace($className)) . '/exceptions.php';
      if (self::loadFile($fileName)) {
        if (self::$throwExceptions and !class_exists($className, false)) {
          throw new ClassNotFoundException(tr('Class not found: "%1"', $className));
        }
        return true;
      }
    }
    $fileName = str_replace('\\', '/', $className) . '.php';
    if (self::loadFile($fileName))
      return true;
    if (self::$throwExceptions) {
      throw new ClassNotFoundException(tr('Class not found: "%1"', $className));
    }
    return false;
  }
}
