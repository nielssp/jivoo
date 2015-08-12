<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\InvalidPropertyException;
use Jivoo\InvalidMethodException;
use Psr\Log\LoggerAwareInterface as LoggerAware;
use Psr\Log\LoggerInterface;

/**
 * A module is part of an application.
 */
abstract class Module implements IEventSubject, LoggerAware {
  /**
   * @var string[] Names of modules required by this module.
   */
  protected $modules = array();

  /**
   * @var App Application associated with module.
   */
  protected $app = null;
  
  /**
   * @var ModuleLoader Collection of loaded modules.
   */
  protected $m;
  
  /**
   * @var \Jivoo\Core\Vendor\VendorLoader Third-party library loader.
   */
  protected $vendor;
  
  /**
   * @var \Jivoo\Core\Cli\Shell Command-line shell (if running in CLI mode).
   */
  protected $shell;
  
  /**
   * @var \Jivoo\Core\Store\StateMap Application persistent state storage.
   */
  protected $state;
  
  /**
   * @var LoggerInterface Application logger.
   */
  protected $logger;

  /**
   * @var Config Module configuration.
   */
  protected $config = null;

  /**
   * @var \Jivoo\Routing\Request|null The Request object if available (provided by
   * {@see \Jivoo\Routing\Routing} if loaded at time of initialization).
   */
  protected $request = null;

  /**
   * @var \Jivoo\Routing\Session|null Session storage object if available (provided by
   * {@see \Jivoo\Routing\Routing} if loaded at time of initialization)
   */
  protected $session = null;

  /**
   * @var \Jivoo\View\View|null Current view if available (provided by
   * {@see \Jivoo\View\View} if loaded at time of initialization)
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
    $this->state = $app->state;
    $this->m = $app->m;
    $this->m->load($this->modules);
    $this->vendor = $app->vendor;
    $this->shell = $app->shell;
    $this->logger = $app->logger;
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
    return null;
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
    $value = $this->__get($property);
    return isset($value);
  }

  /**
   * Unset value of a property.
   * @param string $property Property name.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __unset($property) {
    $this->__set($property, null);
  }

  /**
   * Call a method.
   * @param string $method Method name.
   * @param mixed[] $paramters List of parameters.
   * @return mixed Return value.
   * @throws InvalidMethodException If method is not defined.
   */
  public function __call($method, $parameters) {
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
  }
  
  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }
  
  /**
   * Get logger.
   * @return LoggerInterface Logger.
   */
  public function getLogger() {
    return $this->logger;
  }
  
  /**
   * Combine array property default values from parent classes.
   * @param string Name of property.
   */
  protected function inheritElements($property) {
    $value = $this->$property;
    $parent = new \ReflectionClass(get_parent_class($this));
    while ($parent->name != 'Jivoo\Core\Module') {
      $defaults = $parent->getDefaultProperties();
      if (isset($defaults[$property]) and is_array($defaults[$property]))
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
  public function p($key, $path = null) {
    return $this->app->p($key, $path);
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