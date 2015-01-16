<?php
/**
 * A module is part of an application.
 * @package Jivoo\Core
 */
abstract class Module implements IEventSubject {
  /**
   * @var string[] Names of other modules that this module depends on.
   */
  protected $modules = array();

  /**
   * @var App Application associated with module.
   */
  protected $app = null;
  
  /**
   * @var Map Collection of loaded modules.
   */
  protected $m;

  /**
   * @var AppConfig Module configuration.
   */
  protected $config = null;

  /**
   * @var Request|null The Request object if available (provided by
   * {@see Routing} if loaded at time of initialization).
   */
  protected $request = null;

  /**
   * @var SessionStorage|null Session storage object if available (provided by
   * {@see Routing} if loaded at time of initialization)
   */
  protected $session = null;

  /**
   * @var View|null Current view if available (provided by
   * {@see View} if loaded at time of initialization)
   */
  protected $view = null;
  
  /**
   * @var string[] List of event names fired by this module.
   */
  protected $events = array();

  /**
   * @var EventManager Event manager for this module.
   */
  private $e;

  /**
   * Construct module. Should always be called when extending this class.
   * @param App $app Associated application.
   */
  public function __construct(App $app) {
    $this->app = $app;
    $this->config = $app->config;
    $this->m = $app->getModules($this->modules);
    if (isset($this->m->Routing)) {
      $this->request = $this->m->Routing->request;
      $this->session = $this->request->session;
    }
    if (isset($this->m->View))
      $this->view = $this->m->View;

    $this->e = new EventManager($this, $this->app->eventManager);
  }
  
  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Set value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __set($property, $value) {
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Whether or not a property is set, i.e. not null.
   * @param string $property Property name.
   * @return bool True if not null, false otherwise.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __isset($property) {
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Unset value of a property.
   * @param string $property Property name.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __unset($property) {
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Call a method.
   * @param string $method Method name.
   * @param mixed[] $paramters List of parameters.
   * @throws InvalidMethodException If method is not defined.
   */
  public function __call($method, $parameters) {
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
  }
  
  /**
   * Combine array property default values from parent classes.
   * @param string Name of property.
   */
  protected function inheritElements($property) {
    $value = $this->$property;
    $parent = new ReflectionClass(get_parent_class($this));
    while ($parent->name != 'Module') {
      $defaults = $parent->getDefaultProperties();
      $value = array_merge($value, $defaults[$property]);
      $parent = $parent->getParentClass();
    }
    $this->$property = array_unique($value);
  }

  /**
   * Get the absolute path of a file or directory.
   * @param string $key Location-identifier, e.g. 'app'.
   * @param string $path File or directory name.
   * @return string Absolute path.
   */
  public function p($key, $path) {
    return $this->app->p($key, $path);
  }

  /**
   * Get the absolute path of a file relative to the public directory.
   * @param string $path File name.
   * @return string Path.
   */
  public function w($path = '') {
    return $this->app->w($path);
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
  public function attachEventListener(IEventListener $listener) {
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
  public function detachEventListener(IEventListener $listener) {
    $this->e->detachListener($listener);
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
  public function hasEvent($name) {
    return in_array($name, $this->events);
  }

  /**
   * Trigger an event on this object.
   * @param string $name Name of event.
   * @param Event $event Event object.
   * @return bool False if event was stopped, true otherwise.
   */
  public function triggerEvent($name, Event $event = null) {
    return $this->e->trigger($name, $event);
  }
}