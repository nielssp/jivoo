<?php
/**
 * Library system
 * @package Jivoo\Core
 */
class Lib {

  /**
   * @var array List of paths (strings) to look for classes
   */
  private static $paths = array();

  /**
   * @var array Associative array of module info as returned by getModuleInfo()
   */
  private static $info = array();

  /**
   * @var string|null The last module to be used
   */
  private static $lastModule = null;
  
  /**
   * @var bool Whether or not to throw exceptions
   */
  public static $throwExceptions = true;

  private function __construct() {}

  /**
   * Import a module, i.e. add to include paths
   * @param string $module Module name/path
   * @return boolean True if successful, false otherwise
   */
  public static function import($module) {
    $module = trim($module, '/');
    if (isset(self::$paths[$module])) {
      return true;
    }
    $path = LIB_PATH . ($module != '' ? '/' : '') . $module;
    self::$paths[$module] = $path;
    return true;
  }

  /**
   * Add just an include path
   * @param string $path Path
   */
  public static function addIncludePath($path) {
    self::$paths[] = $path;
  } 

  /**
   * Get information about a module
   * @param string $module Module name
   * @return array|false Array of key/value pairs or false if information
   * unavailable
   */
  public static function getModuleInfo($module) {
    if (isset(self::$info[$module])) {
      return self::$info[$module];
    }
    $parent = null;
    $moduleName = $module;
    if (strpos($module, '/') !== false) {
      $segments = explode('/', $module);
      if (count($segments) >= 2) {
        $parent = implode('/', array_slice($segments, 0, -1));
      }
      $moduleName = $segments[count($segments) - 1];
    }
    $meta = FileMeta::read(
      LIB_PATH . '/' . $module . '/' . $moduleName . '.php');
    if (!$meta OR $meta['type'] != 'module') {
      return false;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = $moduleName;
    }
    if (!isset($meta['version']) AND isset($parent)) {
      $parentInfo = Lib::getModuleInfo($parent);
      $meta['version'] = $parentInfo['version'];
    }
    self::$info[$module] = $meta;
    return $meta;
  }

  /**
   * @var int Number of calls to auto loader
   */
  public static $loadCalls = 0;
  
  /**
   * @var int Number of places looked for classes
   */
  public static $loadIterations = 0;
  
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
    self::$loadCalls++;
    if (isset(self::$lastModule)) {
      $classPath = self::$lastModule . '/' . $className . '.php';
      if (file_exists($classPath)) {
        require $classPath;
        return true;
      }
    }
    $bt = debug_backtrace();
    if (isset($bt[1]) AND isset($bt[1]['file'])) {
      $module = dirname($bt[1]['file']);
      $classPath = $module . '/' . $className . '.php';
      if (file_exists($classPath)) {
        self::$lastModule = $module;
        require $classPath;
        return true;
      }
    }
    foreach (self::$paths as $module => $path) {
      self::$loadIterations++;
      $classPath = $path . '/' . $className . '.php';
      if (file_exists($classPath)) {
        self::$lastModule = $path;
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
 * @package Jivoo\Core
 */
class ClassNotFoundException extends Exception { }

/**
 * Thrown when a class is invalid
 * @package Jivoo\Core
 */
class ClassInvalidException extends Exception { }
