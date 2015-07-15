<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Lib;
use Jivoo\Core\Utilities;

/**
 * Controller module. Will automatically find controllers in the controllers
 * directory (and subdirectories). 
 */
class Controllers extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing');
  
  /**
   * @var Controller[] Associative array of controller instances.
   */
  private $instances = array();
  
  /**
   * @var array An associative array of controller names and actions.
   */
  private $actions = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Routing->dispatchers->add(new ActionDispatcher($this->m->Routing, $this));
    if (is_dir($this->p('app', 'controllers')))
      Lib::import($this->p('app', 'controllers'), $this->app->n('Controllers'));
  }
  
  /**
   * Get class name of controller.
   * @param string $name Controller name.
   * @return string|false Class name or false if not found.
   */
  public function getClass($name) {
    if (isset($this->instances[$name]))
      return get_class($this->instances[$name]);
    $class = $name . 'Controller';
    if (!Lib::classExists($class))
      $class = $this->app->n('Controllers\\' . $class);
    return $class;
  }
  
  /**
   * Get list of actions.
   * @param string $name Controller name.
   * @return string[]|boolean List of actions or false if controller not found. 
   */
  public function getActions($name) {
    $controller = $this->getController($name);
    if (!isset($this->actions[$name])) {
      $class = get_class($controller);
      $reflection = new \ReflectionClass($class);
      $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
      $this->actions[$name] = array();
      foreach ($methods as $method) {
        if ($method->class == $class and $method->name != 'before' and $method->name != 'after')
          $this->actions[$name][] = $method->name;
      }
    }
    return $this->actions[$name];
  }

  /**
   * Get a controller instance.
   * @param string $name Controller name.
   * @param bool $singleton Whether to use an existing instance instead of
   * creating a new one.
   * @return Controller|null Controller object or null if not found.
   */
  public function getController($name, $singleton = true) {
    if (!$singleton or !isset($this->instances[$name])) {
      $class = $name . 'Controller';
      if (!Lib::classExists($class))
        $class = $this->app->n('Controllers\\' . $class);
      Lib::assumeSubclassOf($class, 'Jivoo\Controllers\Controller');
      $object = new $class($this->app);
      if (!$singleton)
        return $object;
      $this->instances[$name] = $object;
    }
    return $this->instances[$name];
  }
}
