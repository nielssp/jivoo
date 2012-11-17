<?php
/**
 * Core application
 * @package PeanutCMS
 */
class Core {
  private $modules = array();
  private static $info = array();
  private $blacklist = array();

  /* EVENTS BEGIN */
  private $events = null;

  /**
   * Event, triggered each time a module is loaded
   * @param callback $h Attach an event handler
   * @uses ModuleLoadedEventArgs
   */
  public function onModuleLoaded($h) { $this->events->attach($h); }
  /**
   * Event, triggered when all modules are loaded
   * @param callback $h Attach an event handler
   */
  public function onModulesLoaded($h) { $this->events->attach($h); }
  /**
   * Event, triggered when ready to render page
   * @param callback $h Attach an event handler
   */
  public function onRender($h) { $this->events->attach($h); }
  /* EVENTS END */

  /**
   * Constructor
   * @param array|string $blacklist An array of modules that
   * should not be loaded or a filename (string) of a file
   * containing a newline separated list of modules.
   */
  public function __construct($blacklist = null) {
    $this->events = new Events($this);

    if (is_array($blacklist)) {
      $this->blacklist = $blacklist;
    }
    else if (is_string($blacklist) AND file_exists($blacklist)) {
      $blacklistFile = file($blacklist);
      foreach ($blacklistFile as $line) {
        $line = trim($line);
        if ($line[0] != '#') {
          $this->blacklist[$this] = true;
        }
      }
    }
  }

  /**
   * Return a loaded module
   * @param string $module Module name
   * @return ModuleBase Module object
   * @throws ModuleNotLoadedException If module is not loaded
   */
  public function __get($module) {
    if (!isset($this->modules[$module])) {
      $backtrace = debug_backtrace();
      $class = $backtrace[1]['class'];
      throw new ModuleNotLoadedException(tr(
        'The "%1" module requests the "%2" module, which is not loaded',
        $class,
        $module
      ));
    }
    return $this->modules[$module];
  }

  /**
   * Request a module
   * @param string $module Module name
   * @return ModuleBase|false Module object or false if module is
   * not loaded
   */
  public function requestModule($module) {
    try {
      return $this->$module;
    }
    catch (ModuleNotLoadedException $e) {
      return false;
    }
  }
  
  /**
   * Get the version of a module
   * @param string $module Module name
   * @return string|false Vesion string or false if information unavailable
   */
  public function getVersion($module) {
    $info = self::getModuleInfo($module);
    if ($info !== false) {
      return $info['version'];
    }
    return false;
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
    $meta = readFileMeta(p(CLASSES . $module . '/' . $module . '.php'));
    if (!$meta OR $meta['type'] != 'module') {
      return false;
    }
    if (!isset($meta['name'])) {
      $meta['name'] = $module;
    }
    self::$info[$module] = $meta;
    return $meta;
  }

  /**
   * Check module dependencies
   * @param ModuleBase|string $module Module object or module name (string)
   * @throws ModuleInvalidException If the module is invalid
   * @throws ModuleMissingDependencyException If a dependency is missing
   */
  public function checkDependencies($module) {
    if (is_subclass_of($module, 'ModuleBase')) {
      $info = self::getModuleInfo(get_class($module));
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

  /**
   * Whether or not a module is blacklisted
   * @return bool True if blacklisted, false if not
   */
  public function onBlacklist($module) {
    $module = $module;
    return isset($this->blacklist[$module]);
  }

  /**
   * Load a module (or return it if it is already loaded)
   * @param string $module Module name
   * @return ModuleBase Module object
   * @throws ModuleBlacklistedException If module is blacklisted
   * @throws ModuleNotFoundException If module does not exist
   * @throws ModuleInvalidException If module is invalid
   * @throws ModuleMissingDependencyException If a dependency is missing
   */
  public function loadModule($module) {
    $module = $module;
    if ($this->onBlacklist($module)) {
      throw new ModuleBlacklistedException(tr('The "%1" module is blacklisted', $module));
    }
    if (!isset($this->modules[$module])) {
      if (!class_exists($module)) {
        if (!file_exists(p(CLASSES . $module . '/' . $module . '.php'))) {
          throw new ModuleNotFoundException(tr('The "%1" module could not be found', $module));
        }
        require(p(CLASSES . $module . '/' . $module . '.php'));
        if (!class_exists($module)) {
          throw new ModuleInvalidException(tr('The "%1" module does not have a main class', $module));
        }
      }
      $info = self::getModuleInfo($module);
      if (!$info) {
        throw new ModuleInvalidException(tr('The "%1" module is invalid', $module));
      }
      $dependencies = $info['dependencies']['modules'];
      $modules = array();
      foreach ($dependencies as $dependency => $versionInfo) {
        $dependency = $dependency;
        try {
          $modules[$dependency] = $this->loadModule($dependency);
        }
        catch (ModuleNotFoundException $e) {
          throw new ModuleMissingDependencyException(tr(
            'The "%1" module depends on the "%2" module, which could not be found',
            $module,
            $dependency
          ));
        }
      }
      $this->modules[$module] = new $module($modules, $this);
    }
    return $this->modules[$module];
  }

  /**
   * Main
   * @param array $modules An array of modules to load
   */
  public static function main($modules) {
    $core = new Core(p(CFG . 'blacklist'));

    foreach ($modules as $module) {
      try {
        $object = $core->loadModule($module);
        $core->events->trigger('onModuleLoaded', new ModuleLoadedEventArgs($module, $object));
      }
      catch (ModuleBlacklistedException $e) {
        // The user has blacklisted this module, continue loading other modules
        continue;
      }
      catch (ModuleNotFoundException $e) {
        if (class_exists('Errors')) {
          Errors::fatal(
            tr('Module not found'),
            $e->getMessage(),
            /** @todo Add useful information, might even be an idea to automatically fix the problem (depending on module) */
            '<p>!!Information about how to fix this problem (as a webmaster) here!!</p>'
             . '<h2>Solution 1: Blacklist "' . $module . '" module</h2>'
             . '<p>Open the file ' . w(CFG . 'blacklist') . ' and add "' . $module . '" to a '
             . 'new line. This will prevent PeanutCMS from attempting to load the module.</p>'
             . '<h2>Solution 2: Reinstall "' . $module . '"</h2>'
          );
        }
      }
    }
    $core->events->trigger('onModulesLoaded');
    $core->events->trigger('onRender');
  }
}

/**
 * Thrown when a requested module is not loaded
 * @package PeanutCMS
 */
class ModuleNotLoadedException extends Exception { }
/**
 * Thrown when a module does not exist
 * @package PeanutCMS
 */
class ModuleNotFoundException extends Exception { }
/**
 * Thrown when a module is invalid
 * @package PeanutCMS
 */
class ModuleInvalidException extends Exception { }
/**
 * Thrown when a module is missing dependencies
 * @package PeanutCMS
 */
class ModuleMissingDependencyException extends ModuleNotFoundException { }
/**
 * Thrown when a module is blacklisted
 * @package PeanutCMS
 */
class ModuleBlacklistedException extends Exception { }

/**
 * EventArgs to be sent with the onModuleLoaded event
 * @property-read string $module Module name
 * @property-read ModuleBase $object Module object
 * @package PeanutCMS
 */
class ModuleLoadedEventArgs extends EventArgs {
  protected $module;
  protected $object;
}
