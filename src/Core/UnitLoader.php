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
  protected $events = array('unitRun', 'unitDone', 'allRun', 'allDone');
  
  /**
   * Construct unit loader.
   * @param App $app Application.
   */
  public function __construct(App $app) {
    parent::__construct($app);
    $app->on('stop', array($this, 'stopAll'));
  }
  
  /**
   * Enable a unit.
   * @param string|Unit|string[]|Unit[] $name Unit(s) to enable, may be a unit
   * name, a {@see Unit} object, or an array of names and objects.
   * @param bool $withDependencies Whether to recursively enable the unit's
   * dependencies.
   */
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
  
  /**
   * Disable a unit.
   * @param string|Unit|string[]|Unit[] $name  Unit(s) to enable, may be a unit
   * name, a {@see Unit} object, or an array of names and objects.
   * @param bool $cascade Whether to recursively disable units that depend on
   * this one.
   */
  public function disable($name, $cascade = false) {
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
      if ($cascade) {
        $this->states[$name] = UnitState::DISABLED;
      }
      else {
        unset($this->states[$name]);
      }
    }
  }

  /**
   * Get the name of a unit. The name of a unit is its class name without a
   * "Unit"-suffix. If the class is in the namespace "Jivoo\Core\Units" or
   * "(app namespace)\Units", the namespace is removed from the name.
   * @param Unit $unit Unit.
   * @return string Unit name.
   */
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
  
  /**
   * Load a unit (does not enable it).
   * 
   * When loading a unit, the method will first look in the namespaces
   * "Jivoo\Core\Units" and "(app namespace)\Units". E.g. when attempting to
   * load "Foo\Bar", the following class lookups are made:
   * "Jivoo\Core\Units\Foo\BarUnit", "(app namespace)\Units\Foo\BarUnit",
   * "Foor\BarUnit", and "Foo\Bar".
   * @param string|Unit $name Unit name or object.
   * @return Unit The loaded unit.
   */
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
  
  /**
   * Get the state of a unit.
   * @param string|Unit $name Unit name or object.
   * @return string Unit state, see {@see UnitState} for possible values.
   */
  public function getState($name) {
    if ($name instanceof Unit)
      $name = $this->getName($name);
    if (!isset($this->states[$name]))
      return UnitState::UNLOADED;
    return $this->states[$name];
  }

  /**
   * Whether a unit is either enabled or has already run.
   * @param string|Unit $name Unit name or object.
   * @return bool True if enabled or done.
   */
  public function isActive($name) {
    $state = $this->getState($name);
    return in_array($state, array(UnitState::ENABLED, UnitState::DONE));
  }

  /**
   * Whether a unit has finished successfully.
   * @param string|Unit $name Unit name or object.
   * @return bool True if done.
   */
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

  /**
   * Run a unit.
   * @param string|Unit $name Unit name or object.
   * @throws LoadOrderException If dependencies cannot be satisfied.
   * @throws Exception Any exception may be thrown from a unit.
   * @return bool True if unit runs (or has already run) successfully. False
   * if disabled or not loaded.
   */
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

    $this->triggerEvent('unitRun', new Event($this, array(
      'unitName' => $name,
      'unit' => $this->units[$name]
    )));
    
    try {
      $config = $this->config->getSubset($name);
      $this->units[$name]->run($this->app, $config);
    }
    catch (\Exception $e) {
      $this->states[$name] = UnitState::FAILED;
      throw $e;
    }
    $this->triggerEvent('unitDone', new Event($this, array(
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
    $this->triggerEvent('allRun');
    while (count($this->waiting)) {
      $this->run(array_shift($this->waiting));
    }
    $this->triggerEvent('allDone');
  }
  
  /**
   * Stop all enabled units.
   */
  public function stopAll() {
    foreach ($this->states as $name => $state) {
      if ($state == UnitState::DONE) {
        $config = $this->config->getSubset($name);
        $this->units[$name]->stop($this->app, $config);
      }
    }
  }
}
