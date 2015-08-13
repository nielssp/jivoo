<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Loads and keeps track of Jivoo modules (subclasses of {@see LoadableModule}).
 * @property-read \Jivoo\AccessControl\AccessControl $AccessControl
 * @property-read \Jivoo\ActiveModels\ActiveModels $ActiveModels
 * @property-read \Jivoo\Assets\Assets $Assets
 * @property-read \Jivoo\Console\Console $Console
 * @property-read \Jivoo\Content\Content $Content
 * @property-read \Jivoo\Controllers\Controllers $Controllers
 * @property-read \Jivoo\Databases\Databases $Databases
 * @property-read \Jivoo\Extensions\Extensions $Extensions
 * @property-read \Jivoo\Helpers\Helpers $Helpers
 * @property-read \Jivoo\Jtk\Jtk $Jtk
 * @property-read \Jivoo\Migrations\Migrations $Migrations
 * @property-read \Jivoo\Models\Models $Models
 * @property-read \Jivoo\Routing\Routing $Routing
 * @property-read \Jivoo\Setup\Setup $Setup
 * @property-read \Jivoo\Snippets\Snippets $Snippets
 * @property-read \Jivoo\Themes\Themes $Themes
 * @property-read \Jivoo\View\View $View
 */
class ModuleLoader implements EventSubject {
  /**
   * @var App
   */
  private $app;

  /**
   * @var LoadableModule[]
   */
  private $modules = array();

  /**
   * @var ObjectMacro[]
   */
  private $lazyModules = array();
  
  /**
   * @var string[]
   */
  private $classes = array();
  
  /**
   * @var string[][]
   */
  private $before = array();
  
  /**
   * @var string[][]
   */
  private $after = array();
  
  /**
   * @var EventManager Application event manager.
   */
  private $e = null;
  
  /**
   * @var string[]
   */
  private $events = array('beforeLoadModule', 'afterLoadModule');
  
  /**
   * Construct module loader.
   * @param App $app Application.
   */
  public function __construct(App $app) {
    $this->app = $app;
    $this->e = new EventManager($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents() {
    return $this->events;
  }
  
  /**
   * {@inheritdoc}
   */
  public function attachEventHandler($name, $callback) {
    $this->e->attachHandler($name, $callback);
  }
  
  /**
   * {@inheritdoc}
   */
  public function attachEventListener(EventListener $listener) {
    $this->e->attachListener($listener);
  }
  
  /**
   * {@inheritdoc}
   */
  public function detachEventHandler($name, $callback) {
    $this->e->detachHandler($name, $callback);
  }
  
  /**
   * {@inheritdoc}
   */
  public function detachEventListener(EventListener $listener) {
    $this->e->detachListener($listener);
  }
  
  /**
   * {@inheritdoc}
   */
  public function hasEvent($name) {
    return in_array($name, $this->events);
  }
  
  /**
   * @param string $name
   * @param Event $event
   * @return bool
   */
  private function triggerEvent($name, Event $event = null) {
    return $this->e->trigger($name, $event);
  }
  
  /**
   * Load a module.
   * @param string $module Module name.
   * @return LoadableModule Module.
   */
  public function __get($module) {
    return $this->load($module);
  }
  
  /**
   * Whether a module has been loaded.
   * @param string $module Module name.
   * @return bool True if module is loaded.
   */
  public function __isset($module) {
    return isset($this->modules[$module]);
  }
  
  /**
   * Whether a module has been imported (but not necessarily loaded).
   * @param string $module Module name.
   * @return bool True if module is imported.
   */
  public function hasImport($module) {
    return isset($this->classes[$module]);
  }
  
  /**
   * Get a lazy instance of a module if the module has not already been loaded.
   * The lazy instance will record uses of setters and methods.
   * @param string $module Module name.
   * @return ObjectMacro|LoadableModule A lazy module (see {@see ObjectMacro})
   * or a module if already loaded.
   */
  public function lazy($module) {
    if (isset($this->modules[$module]))
      return $this->modules[$module];
    if (!isset($this->lazyModules[$module]))
      $this->lazyModules[$module] = new ObjectMacro();
    return $this->lazyModules[$module];
  }
  
  /**
   * Ensures that moduleA is loaded before moduleB.
   * @param string $moduleA Module A.
   * @param string $moduleB Module B.
   */
  public function before($moduleA, $moduleB) {
    if (!isset($this->before[$moduleA]))
      $this->before[$moduleA] = array();
    $this->before[$moduleA][] = $moduleB;
    if (!isset($this->after[$moduleB]))
      $this->after[$moduleB] = array();
    $this->after[$moduleB][] = $moduleA;
  }
  
  /**
   * Import a module. Importing all modules before they are loaded ensures
   * correct load order.
   * @param string|string[] $module Module name or list of module names.
   */
  public function import($module) {
    if (is_array($module)) {
      foreach ($module as $m)
        $this->import($m);
      return;
    }
    if (isset($this->classes[$module]))
      return;
    if (strpos($module, '\\') === false) {
      $class = 'Jivoo\\' . $module . '\\' . $module;
      $pathName = $module;
      $this->app->paths->$module = \Jivoo\PATH . '/' . $module;
    }
    else {
      $class = $module;
      $components = explode('\\', $class);
      $module = array_pop($components);
      if ($components == array('Jivoo', $module))
        $pathName = $module;
      else
        $pathName = $class;
    }
    $this->classes[$module] = $class;
    
    assume(class_exists($class));
    $loadOrder = LoadableModule::getLoadOrder($class);

    foreach ($loadOrder['before'] as $dependency)
      $this->before($module, $dependency);

    foreach ($loadOrder['after'] as $dependency)
      $this->before($dependency, $module);
  }

  /**
   * Load a module.
   * @param string|string[] $module Module name or list of module names.
   * @return LoadableModule Module.
   */
  public function load($module) {
    if (is_array($module)) {
      $this->import($module);
      foreach ($module as $m)
        $this->load($m);
      return;
    }
    if (!isset($this->modules[$module])) {
      $this->triggerEvent('beforeLoadModule', new LoadModuleEvent($this, $module));
      if (!isset($this->classes[$module]))
        $this->import($module);
      $class = $this->classes[$module];
      if (isset($this->after[$module])) {
        foreach ($this->after[$module] as $dependency) {
          if (isset($this->classes[$dependency]))
            $this->load($dependency);
        }
      }
      if (isset($this->before[$module])) {
        foreach ($this->before[$module] as $dependency) {
          if (isset($this->modules[$dependency])) {
            throw new LoadOrderException(tr('%1 must load before %2', $module, $dependency));
          }
        }
      }
      Utilities::assumeSubclassOf($class, 'Jivoo\Core\LoadableModule');
      $this->modules[$module] = new $class($this->app);
      $this->triggerEvent('afterLoadModule', new LoadModuleEvent($this, $module, $this->modules[$module]));
      $this->modules[$module]->afterLoad();
      if (isset($this->lazyModules[$module]))
        $this->lazyModules[$module]->playMacro($this->modules[$module], false);
    }
    return $this->modules[$module];
  }
}

/**
 * Event sent before and after a module has been loaded
 */
class LoadModuleEvent extends LoadEvent { }