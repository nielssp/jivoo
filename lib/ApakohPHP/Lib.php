<?php
class Lib {

  private static $paths = array();

  private static $classes = array();

  private static $info = array();

  private function __construct() {
  }
  
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
    $meta = readFileMeta(LIB_PATH . '/' . $module . '/' . $moduleName . '.php');
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
    //echo 'LOAD ' . $className . PHP_EOL;
    foreach (self::$paths as $module => $path) {
      $classPath = $path . '/' . $className . '.php';
      //echo 'TEST ' . $classPath . PHP_EOL;
      if (file_exists($classPath)) {
        require $classPath;
        return true;
      }
    }
    return false;
  }
}
