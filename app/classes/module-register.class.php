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
    if (!isset(self::$register[$moduleName])) {
      return FALSE;
    }
    return self::$register[$moduleName];
  }

  public static function onRegister($moduleName, $callback) {
    if (!isset(self::$events[$moduleName])) {
      self::$events[$moduleName] = array();
    }
    if (isset(self::$register[$moduleName])) {
      if (is_callable($callback)) {
        call_user_func($callback, self::$register[$moduleName]);
      }
    }
    self::$events[$moduleName][] = $callback;
  }
}