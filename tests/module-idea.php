<?php

// Base class for modules/controllers/models/helpers
abstract class Module {
  // supports events -[and behaviours]
  // no more filemeta on every load?
  
  protected $modules = array();
  
  protected $app = null;
  protected $m;
  
  /**
   * @var AppConfig Module configuration
   */
  protected $config = null;

  /**
   * @var Request|null The Request object if available
   */
  protected $request = null;
  
  /**
   * @var SessionStorage|null Session storage object if available
   */
  protected $session = null;
  
  /**
   * @var View|null Current view if available
   */
  protected $view = null;
  
  private $e;
  
  public function __construct(App $app) {
    $this->app = $app;
    $this->config = $app->config;
    $this->m = $app->getModules($this->modules);
    if (isset($this->m->Routing)) {
      $this->request = $this->m->Routing->request;
      $this->session = $this->request->session;
    }
    if (isset($this->m->Templates))
      $this->view = $this->m->Templates->view;

    $this->e = new EventManager($this);
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
    $this->e->attach($event, $callback);
  }
  
  public function addEventListener(IEventListener $listener) {
    foreach ($listener->getEvents() as $event => $method) {
      $this->e->$event[] = array($listener, $method);
    }
  }
  
  protected function trigger($event, $eventArgs = null) {
    $this->e->trigger($event, $eventArgs);
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

/**
 * Collection of events and handlers
 * @package Core
 */
class EventManager {
  /**
   * @var array Associative array where the key is an event name and the value
   * is an array of callbacks
   */
  private $events = array();

  /**
   * @var object The object that triggers events in this collection
  */
  private $sender = null;

  /**
   * Create a new event collection
   * @param object $sender The object that triggers events in this collection
   */
  public function __construct($sender) {
    $this->sender = $sender;
  }

  /**
   * Attach a handler to an event.
   * @param callback $handler A function of type
   * `function eventHandler(Event $event)`
   */
  public function attach($handler = null) {
    $backtrace = debug_backtrace();
    if (isset($backtrace[1]['function'])) {
      if (!isset($handler)) {
        $handler = $backtrace[1]['args'][0];
      }
      $event = $backtrace[1]['function'];
      if (!isset($this->events[$event])) {
        $this->events[$event] = array();
      }
      $this->events[$event][] = $handler;
    }
  }

  /**
   * Execute all functions attached to an event
   * @param string $event Event name
   * @param mixed $eventArgs Event arguments
   */
  public function trigger($name, Event $event = null) {
    if (isset($this->events[$name])) {
      if (!isset($event))
        $event = new Event($this->sender);
      foreach ($this->events[$name] as $function) {
        call_user_func($function, $event);
        if ($event->stopped)
          return;
      }
    }
  }
}

class Event {
  
  private $stopped = false;
  private $sender = null;
  private $parameters = array();
  
  /**
   * Constructor.
   */
  public function __construct($sender = null, $parameters = array()) {
    $this->sender = $sender;
    $this->parameters = $parameters;
  }
  
  /**
   * Get the value of a property.
   * @param string $property Name of property
   * @return mixed Value of property
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
  }
  
  public function stopPropagation() {
    $this->stopped = true;
  }
}

interface IEventListener {
  public function getEvents();
}
