<?php
class Lib {

  private static $paths = array();

  private static $info = array();

  private static $lastModule = null;

  private function __construct() {}

  public static function import($module) {
    $module = trim($module, '/');
    if (isset(self::$paths[$module])) {
      return true;
    }
    $path = LIB_PATH . ($module != '' ? '/' : '') . $module;
    self::$paths[$module] = $path;
    return true;
  }

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

  public static $loadCalls = 0;
  public static $loadIterations = 0;
  
  public static $throwExceptions = true;
  
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
      throw new ClassNotFoundException(tr('Class not found: %1', $className));
    }
    return false;
  }
}

class ClassNotFoundException extends Exception { }
