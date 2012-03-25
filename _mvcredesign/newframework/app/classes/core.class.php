<?php

class Core {
  private $modules = array();
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
      require_once(p(MODULES . $module . '.class.php'));
      $className = fileClassName($module);
      if (!class_exists($className)) {
        throw new ModuleInvalidException(tr('The "%1" module does not have a class', $module));
      }
      $reflection = new ReflectionClass($className);
      if (!$reflection->implementsInterface('IModule')) {
        throw new ModuleInvalidException(tr('The "%1" module is invalid', $module));
      }
      $dependencies = call_user_func(array($className, 'getDependencies'), $this);
      $arguments = array();
      foreach ($dependencies as $dependency) {
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
      $this->modules[$module] = $reflection->newInstanceArgs($arguments);
    }
    return $this->modules[$module];
  }
}

class ModuleNotFoundException extends Exception { }
class ModuleInvalidException extends Exception { }
class ModuleMissingDependencyException extends ModuleNotFoundException { }
class ModuleBlacklistedException extends Exception { }