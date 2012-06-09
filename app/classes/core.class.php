<?php

class Core {
  private $modules = array();
  private static $info = array();
  private $blacklist = array();

  public function __construct($blacklist = NULL) {
    if (is_array($blacklist)) {
      $this->blacklist = $blacklist;
    }
    else if (is_string($blacklist) AND file_exists($blacklist)) {
      $blacklistFile = file($blacklist);
      foreach ($blacklistFile as $line) {
        $line = trim($line);
        if ($line[0] != '#') {
          $this->blacklist[] = $line;
        }
      }
    }
  }

  public function __get($module) {
    if (!isset($this->modules[$module])) {
      $backtrace = debug_backtrace();
      $class = classFileName($backtrace[1]['class']);
      throw new ModuleNotLoadedException(tr(
        'The "%1" module requests the "%2" module, which is not loaded',
        $class,
        $module
      ));
    }
    return $this->modules[$module];
  }

  public function requestModule($module) {
    try {
      return $this->$module;
    }
    catch (ModuleNotLoadedException $e) {
      return FALSE;
    }
  }
  
  public function getVersion($module) {
    $info = self::getModuleInfo($module);
    if ($info !== FALSE) {
      return $info['version'];
    }
    return FALSE;
  }

  public static function getModuleInfo($module) {
    if (isset(self::$info[$module])) {
      return self::$info[$module];
    }
    $meta = readFileMeta(p(MODULES . $module . '.class.php'));
    if (!$meta OR $meta['type'] != 'module') {
      return FALSE;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = fileClassName($module);
    }
    self::$info[$module] = $meta;
    return $meta;
  }

  public function checkDependencies($module) {
    if (is_subclass_of($module, 'IModule')) {
      $info = self::getModuleInfo(classFileName(get_class($module)));
    }
    else {
      $info = self::getModuleInfo($module);
    }
    if (!$info) {
      throw new ModuleInvalidException(tr('The "%1" module is invalid', $module));
    }
    $missing = array();
    foreach ($info['dependencies'] as $dependency) {
      if (!isset($this->modules[$dependency])) {
        $missing[] = $dependency;
      }
    }
    if (count($missing) > 0) {
      throw new ModuleMissingDependencyException(trl(
        'The "%1" module depends on the "%l" module',
        'The "%1" module depends on the "%l" modules',
        '", "', '" and "', $missing, $info['name']
      ));
    }
  }

  public function onBlacklist($module) {
    return in_array($module, $this->blacklist);
  }

  public function loadModule($module) {
    if ($this->onBlacklist($module)) {
      throw new ModuleBlacklistedException(tr('The "%1" module is blacklisted', $module));
    }
    if (!isset($this->modules[$module])) {
      if (!file_exists(p(MODULES . $module . '.class.php'))) {
        throw new ModuleNotFoundException(tr('The "%1" module could not be found', $module));
      }
      require(p(MODULES . $module . '.class.php'));
      $className = fileClassName($module);
      if (!class_exists($className)) {
        throw new ModuleInvalidException(tr('The "%1" module does not have a main class', $module));
      }
      //$reflection = new ReflectionClass($className);
      //if (!$reflection->implementsInterface('IModule')) {
      //  throw new ModuleInvalidException(tr('The "%1" module is invalid', $module));
      //}
      $info = self::getModuleInfo($module);
      if (!$info) {
        throw new ModuleInvalidException(tr('The "%1" module is invalid', $module));
      }
      $dependencies = $info['dependencies']['modules'];
      $arguments = array();
      foreach ($dependencies as $dependency => $versionInfo) {
        try {
          $arguments[] = $this->loadModule($dependency);
        }
        catch (ModuleNotFoundException $e) {
          throw new ModuleMissingDependencyException(tr(
            'The "%1" module depends on the "%2" module, which could not be found',
            $module,
            $dependency
          ));
        }
      }
      //$this->modules[$module] = $reflection->newInstanceArgs(array($this));
      $this->modules[$module] = new $className($this);
    }
    return $this->modules[$module];
  }
}

class ModuleNotLoadedException extends Exception { }
class ModuleNotFoundException extends Exception { }
class ModuleInvalidException extends Exception { }
class ModuleMissingDependencyException extends ModuleNotFoundException { }
class ModuleBlacklistedException extends Exception { }
