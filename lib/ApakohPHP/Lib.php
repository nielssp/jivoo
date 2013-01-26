<?php
class Lib {

  private static $paths = array();

  private static $info = array();

  private static $lastModule = null;

  private function __construct() {}

  public static function import($module) {
    if (isset(self::$paths[$module])) {
      return true;
    }
    $module = trim($module, '/');
    $path = LIB_PATH . ($module != '' ? '/' : '') . $module;
    //    echo 'IMPORT ' . $module . ' > ' . $path . PHP_EOL;
    self::$paths[$module] = $path;
    return true;
  }

  public static function addIncludePath($path) {
    //    echo 'ADDPATH ' . $path . PHP_EOL;
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
    $moduleName = $module;
    if (strpos($module, '/') !== false) {
      $segments = explode('/', $module);
      $moduleName = $segments[count($segments) - 1];
    }
    //echo 'INFO ' . $module . ' > ' . $moduleName . PHP_EOL;
    $meta = FileMeta::read(
      LIB_PATH . '/' . $module . '/' . $moduleName . '.php');
    if (!$meta OR $meta['type'] != 'module') {
      return false;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = $moduleName;
    }
    self::$info[$module] = $meta;
    return $meta;
  }

  public static $loadCalls = 0;
  public static $loadIterations = 0;

  public static function autoload($className) {
    //echo 'LOAD ' . $className . PHP_EOL;
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
    return false;
  }
}
