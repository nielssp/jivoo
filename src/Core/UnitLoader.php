<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Core\Init\Unit;
use Jivoo\Core\Store\Document;

/**
 * Loads and keeps track of Jivoo initialization units.
 */
class UnitLoader implements EventSubject {
  /**
   * @var App
   */
  private $app;
  
  /**
   * @var Unit[] Loaded units.
   */
  private $units = array();
  
  /**
   * @var bool[] Enabled units.
   */
  private $enabled = array();
  
  /**
   * @var bool[] Finished units.
   */
  private $finished = array();
  
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
  private $events = array('beforeRunUnit', 'afterRunUnit');
  
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
  
  public function enable($name, $withDependencies = false) {
    if (is_array($name)) {
      foreach ($name as $n)
        $this->enable($n, $withDependencies);
      return;
    }
    if ($name instanceof Unit) {
      $name = $this->getName($this->load($name));
    }
    else {
      $this->load($name);
    }
    if ($withDependencies) {
      $this->enable($this->units[$name]->requires(), true);
    }
    $this->enabled[$name] = true;
  }
  
  public function disable($name) {
    if ($name instanceof Unit)
      $name = $this->getName($name);
    if (isset($this->enabled[$name]))
      unset($this->enabled[$name]);
  }

  public function getName(Unit $unit) {
    $class = get_class($unit);
    if (Unicode::startsWith($class , 'Jivoo\Core\Units\\'))
      return substr($class, strlen('Jivoo\Core\Units\\'));
    $ns = $this->app->n() . '\\';
    if (Unicode::startsWith($class , $ns))
      return substr($class, strlen($ns));
    return $class;
  }
  
  public function load($name) {
    if ($name instanceof Unit) {
      $unit = $name;
      $name = $this->getName($unit);
      if (isset($this->units[$name]))
        return $this->units[$name];
    }
    else {
      if (isset($this->units[$name]))
        return $this->units[$name];
      
      if (class_exists('Jivoo\Core\Units\\' . $name))
        $class = 'Jivoo\Core\Units\\' . $name;
      else if (class_exists($this->app->n('Units\\' . $name)))
        $class = $this->app->n('Units\\' . $name);
      else
        $class = $name;
      Assume::isSubclassOf($class, 'Jivoo\Core\UnitBase');
      $unit = new $class($this->app);
    }
    $this->units[$name] = $unit;

    foreach ($unit->before() as $dependency)
      $this->before($name, $dependency);
    foreach ($unit->after() as $dependency)
      $this->before($dependency, $name);
    
    return $unit;
  }

  public function isEnabled($name) {
    if ($name instanceof Unit)
      $name = $this->getName($name);
    return isset($this->enabled[$name]);
  }

  public function isFinished($name) {
    if ($name instanceof Unit)
      $name = $this->getName($name);
    return isset($this->finished[$name]);
  }
  
  /**
   * Ensures that $unitA runs before $unitB.
   * @param string $unitA Unit A.
   * @param string $unitB Unit B.
   */
  public function before($unitA, $unitB) {
    if (!isset($this->before[$unitA]))
      $this->before[$unitA] = array();
    $this->before[$unitA][] = $unitB;
    if (!isset($this->after[$unitB]))
      $this->after[$unitB] = array();
    $this->after[$unitB][] = $unitA;
  }

  public function run($name) {
    if (!isset($this->enabled[$name])) {
      return false;
    }
    if (!isset($this->units[$name]))
      return false;

    foreach ($this->units[$name]->requires() as $dependency) {
      if (!isset($this->finished[$dependency]) and !$this->run($dependency))
        throw new LoadOrderException(tr('Unit %1 depends on %2', $name, $dependency));
    }

    if (isset($this->after[$name])) {
      foreach ($this->after[$name] as $dependency) {
        if (isset($this->enabled[$dependency]))
          $this->run($dependency);
      }
    }
    if (isset($this->before[$name])) {
      foreach ($this->before[$name] as $dependency) {
        if (isset($this->finished[$dependency])) {
          throw new LoadOrderException(tr('Unit %1 must run before %2', $name, $dependency));
        }
      }
    }
    $this->units[$name]->run($this->app, new Document());
    $this->finished[$name] = true;
    unset($this->enabled[$name]);
  }
  
  /**
   * Run all enabled units.
   */
  public function runAll() {
    while (count($this->enabled)) {
      $this->run(key($this->enabled));
    }
  }
}
