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
class UnitLoader extends Module {
  /**
   * @var Unit[] Loaded units.
   */
  private $units = array();
  
  /**
   * @var string[] Waiting units.
   */
  private $waiting = array();
  
  /**
   * @var string[]
   */
  private $states = array();
  
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
   * {@inheritdoc}
   */
  protected $events = array('beforeRunUnit', 'afterRunUnit');
  
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
    $this->states[$name] = UnitState::ENABLED;
    $this->waiting[$name] = $name;
  }
  
  public function disable($name, $unload = true) {
    if (is_array($name)) {
      foreach ($name as $n)
        $this->disable($n, $unload);
      return;
    }
    if ($name instanceof Unit)
      $name = $this->getName($name);
    if (isset($this->states[$name])) {
      if (isset($htis->waiting[$name]))
        unset($this->waiting[$name]);
      if ($unload) {
        unset($this->states[$name]);
      }
      else {
        $this->states[$name] = UnitState::DISABLED;
      }
    }
  }

  public function getName(Unit $unit) {
    $class = get_class($unit);
    if (Unicode::endsWith($class, 'Unit')) {
      $class = substr($class, 0, -4);
      if (Unicode::startsWith($class , 'Jivoo\Core\Units\\'))
        return substr($class, strlen('Jivoo\Core\Units\\'));
      $ns = $this->app->n() . '\\';
      if (Unicode::startsWith($class , $ns))
        return substr($class, strlen($ns));
    }
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
      
      if (class_exists('Jivoo\Core\Units\\' . $name . 'Unit'))
        $class = 'Jivoo\Core\Units\\' . $name . 'Unit';
      else if (class_exists($this->app->n('Units\\' . $name . 'Unit')))
        $class = $this->app->n('Units\\' . $name . 'Unit');
      else if (class_exists($name . 'Unit'))
        $class = $name . 'Unit';
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
  
  public function getState($name) {
    if ($name instanceof Unit)
      $name = $this->getName($name);
    if (!isset($this->states[$name]))
      return UnitState::UNLOADED;
    return $this->states[$name];
  }

  public function isActive($name) {
    $state = $this->getState($name);
    return in_array(array(UnitState::ENABLED, UnitState::DONE), $state);
  }

  public function isDone($name) {
    return $this->getState($name) == UnitState::DONE;
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
    if (!isset($this->units[$name]))
      return false;
    if ($this->states[$name] == UnitState::DONE)
      return true;
    if ($this->states[$name] != UnitState::ENABLED)
      return false;
    
    foreach ($this->units[$name]->requires() as $dependency) {
      if ($this->run($dependency))
        continue;
      $state = $this->getState($dependency);
      if ($state == UnitState::DISABLED) {
        $this->states[$name] = UnitState::DISABLED;
        return false;
      }
      $this->states[$name] = UnitState::FAILED;
      throw new LoadOrderException(
        'Unit ' . $name . ' depends on ' . $dependency . ' (' . $state . ')'
      );
    }

    if (isset($this->after[$name])) {
      foreach ($this->after[$name] as $dependency) {
        if (isset($this->waiting[$dependency]))
          $this->run($dependency);
      }
    }
    if (isset($this->before[$name])) {
      foreach ($this->before[$name] as $dependency) {
        if ($this->getState($dependency) == UnitState::DONE) {
          throw new LoadOrderException(
            'Unit ' . $name . ' must run before ' . $dependency
          );
        }
      }
    }

    $this->triggerEvent('beforeRunUnit', new Event($this, array(
      'unitName' => $name,
      'unit' => $this->units[$name]
    )));
    
    try {
      $this->units[$name]->run($this->app, new Document());
    }
    catch (\Exception $e) {
      $this->states[$name] = UnitState::FAILED;
      throw $e;
    }
    $this->triggerEvent('afterRunUnit', new Event($this, array(
      'unitName' => $name,
      'unit' => $this->units[$name]
    )));
    $this->states[$name] = UnitState::DONE;
    return true;
  }
  
  /**
   * Run all enabled units.
   */
  public function runAll() {
    while (count($this->waiting)) {
      $this->run(array_shift($this->waiting));
    }
  }
}
