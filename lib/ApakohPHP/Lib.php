<?php
class Lib {

  private static $paths = array();

  private static $classes = array();

  private static $info = array();

  private function __construct() {
  }

  public static function import($class) {
    if (isset(self::$classes[$class])) {
      return true;
    }
    if (class_exists($class, false) OR interface_exists($class, false)) {
      return true;
    }
    if (strpos($class, '.') === false AND $class != '*') {
      if (file_exists(LIB_PATH . '/' . $class . '.php')) {
        require LIB_PATH . '/' . $class . '.php';
        self::$classes[$class] = $className;
        return true;
      }
      return false;
    }
    $segments = explode('.', $class);
    $className = $segments[count($segments) - 1];
    if ($className == '*') {
      array_pop($segments);
      $path = LIB_PATH . '/' . implode('/', $segments);
      if (isset(self::$paths[$path])) {
        return true;
      }
      self::$paths[$path] = implode('.', $segments);
      return true;
    }
    else {
      $path = LIB_PATH . '/' . implode('/', $segments) . '.php';
      if (file_exists($path)) {
        require $path;
        self::$classes[$class] = $className;
        return true;
      }
    }
    return false;
  }

  public static function addIncludePath($path) {
    self::$paths[$path] = 'app';
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
    $moduleName = $module;
    if (strpos($module, '.') !== false) {
      $segments = explode('.', $module);
      $moduleName = $segments[count($segments) - 1];
    }
    $meta = readFileMeta(LIB_PATH . '/' . implode('/', $segments) . '/' . $moduleName . '.php');
    if (!$meta OR $meta['type'] != 'module') {
      return false;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = $moduleName;
    }
    self::$info[$module] = $meta;
    return $meta;
  }

  public static function autoload($className) {
    foreach (self::$paths as $path => $dotPath) {
      $classPath = $path . '/' . $className . '.php';
      if (file_exists($classPath)) {
        require $classPath;
        self::$classes[$dotPath . '.' . $className] = $className;
        return true;
      }
    }
    return false;
  }
}
