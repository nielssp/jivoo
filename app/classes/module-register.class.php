<?php

class ModuleRegister {

  private static $register = array();

  private static $events = array();

  private function __construct() {
  }

  public static function register(IModule $module) {
    $moduleName = classFileName(get_class($module));
    self::$register[$moduleName] = $module;
    if (isset(self::$events[$moduleName])) {
      foreach (self::$events[$moduleName] as $callback) {
        if (is_callable($callback)) {
          call_user_func($callback, $module);
        }
      }
    }
  }

  public static function getModule($moduleName) {
    if (!isset($register[$moduleName])) {
      return FALSE;
    }
    return $register[$moduleName];
  }

  public static function onRegister($moduleName, $callback) {
    if (!isset(self::$events[$moduleName])) {
      self::$events[$moduleName] = array();
    }
    self::$events[$moduleName][] = $callback;
  }
}