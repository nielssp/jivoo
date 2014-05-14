<?php

// Base class for modules/controllers/models/helpers
abstract class Module {
  // supports events and behaviours
  // app-dependency
  // no more filemeta on every load?
  
  protected $modules = array();

  protected $behaviors = array();
  
  protected $events = array();
  
  protected $app = null;
  
  private $e;
  
  public function __construct(App $app) {
    $this->app = $app;
    $this->e = new Events($this);
  }

  /**
   * Get the absolute path of a file.
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path) {
    return $this->app->p($key, $path);
  }
  
  /**
   * Get the absolute path of a file relative to the public directory
   * @param string $path File
   * @return string Path
   */
  public function w($path = '') {
    return $this->app->w($path);
  }
  
  public function addEventHandler($event, $callback) {
    if (in_array($event, $this->events)) {
      $this->e->attach($callback);
    }
  }
  
  protected function trigger($event, $eventArgs = null) {
    $this->e->trigger($event, $eventArgs);
  }
  
  public function attachBehavior(IBehavior $behavior) {
    
  }
}

abstract class LoadableModule extends Module {
  
  public final function __construct(App $app) {
    parent::__construct($app);
    $this->init();
  }
  
  protected function init() {
    
  }

  /**
   * Get the absolute path of a file.
   * If called with a single parameter, then the name of the current module
   * is used as location identifier.
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path = null) {
    if (isset($path))
      return $this->p($key, $path);
    return $this->p(get_class($this), $key);
  }
}

interface IBehavior {
  public function attach(Module $module);
  public function detach(Module $module);
}