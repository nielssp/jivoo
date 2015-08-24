<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

use Jivoo\Routing\Dispatcher;
use Jivoo\Routing\Routing;
use Jivoo\Routing\InvalidResponseException;
use Jivoo\Routing\Response;
use Jivoo\Routing\InvalidRouteException;
use Jivoo\Routing\RoutingTable;
use Jivoo\Core\Utilities;
use Jivoo\InvalidClassException;
use Jivoo\Routing\Http;
use Jivoo\Core\Assume;
use Jivoo\Core\Module;

/**
 * Action based routing.
 */
class ActionDispatcher extends Module implements Dispatcher {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('routing');

  /**
   * @var Controller[] Associative array of controller instances.
   */
  private $instances = array();
  
  /**
   * @var array An associative array of controller names and actions.
   */
  private $actions = array();

  /**
   * Get class name of controller.
   * @param string $name Controller name.
   * @return string|false Class name or false if not found.
   */
  public function getClass($name) {
    if (isset($this->instances[$name]))
      return get_class($this->instances[$name]);
    $class = $name . 'Controller';
    if (!class_exists($class))
      $class = $this->app->n('Controllers\\' . $class);
    return $class;
  }
  
  /**
   * Get list of actions.
   * @param string $name Controller name.
   * @return string[]|boolean List of actions or false if controller not found.
   */
  public function getActions($name) {
    $class = $this->getClass($name);
    if (!isset($this->actions[$name])) {
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
      if (!class_exists($class))
        $class = $this->app->n('Controllers\\' . $class);
      if (!class_exists($class))
        return null;
      Assume::isSubclassOf($class, 'Jivoo\Controllers\Controller');
      $object = new $class($this->app);
      if (!$singleton)
        return $object;
      $this->instances[$name] = $object;
    }
    return $this->instances[$name];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('action');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    if (isset($route['controller']) or isset($route['action'])) {
      if (!isset($route['controller'])) {
        $current = $this->m->routing->route;
        if (isset($current['controller']))
          $route['controller'] = $current['controller'];
      }
      if (!isset($route['parameters']))
        $route['parameters'] = array();
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function autoRoute(RoutingTable $table, $route, $resource = false) {
    $controller = $route['controller'];
    $dirs = explode('\\', $controller);
    if ($dirs == array('App'))
      $dirs = array();
    else
      $dirs = array_map(array('Jivoo\Core\Utilities', 'camelCaseToDashes'), $dirs);
    $patternBase = implode('/', $dirs);
    if ($resource) {
      $table->match($patternBase, 'action:' . $controller . '::index');
      $table->match($patternBase . '/add', 'action:' . $controller . '::add'); //C
      $table->match($patternBase . '/:0', 'action:' . $controller . '::view'); //R
      $table->match($patternBase . '/:0/edit', 'action:' . $controller . '::edit'); //U
      $table->match($patternBase . '/:0/delete', 'action:' . $controller . '::delete'); //D
      
      $table->match('DELETE ' . $patternBase . '/:0', 'action:' . $controller . '::delete');
      $table->match('PATCH ' . $patternBase . '/:0', 'action:' . $controller . '::edit');
      $table->match('PUT ' . $patternBase . '/:0', 'action:' . $controller . '::edit');
      $table->match('POST ' . $patternBase, 'action:' . $controller . '::add');
      return $patternBase . '/:0';
    }
    else {
      if (isset($route['action'])) {
        $action = $route['action'];
        $class = $this->getClass($controller);
        if (!$class) {
          throw new InvalidClassException(tr('Invalid controller: %1', $controller));
        }
        $route = array(
          'controller' => $controller,
          'action' => $action
        );
        $reflect = new \ReflectionMethod($class, $action);
        $required = $reflect->getNumberOfRequiredParameters();
        $total = $reflect->getNumberOfParameters();
        if (!empty($prefix) AND substr($prefix, -1) != '/') {
          $prefix .= '/';
        }
        if ($action == 'index') {
          $table->match($patternBase, $route);
        }
        $patternBase .= '/' . str_replace('_', '.', Utilities::camelCaseToDashes($action));
        if ($required < 1) {
          $table->match($patternBase, $route);
        }
        $path = $patternBase;
        for ($i = 0; $i < $total; $i++) {
          $path .= '/*';
          if ($i <= $required) {
            $table->match($path, $route);
          }
        }
        return $patternBase;
      }
      else {
        $actions = $this->getActions($controller);
        if ($actions === false) {
          throw new InvalidClassException(tr('Invalid controller: %1', $controller));
        }
        foreach ($actions as $action) {
          $route['action'] = $action;
          $this->autoRoute($table, $route, false);
        }
        return $patternBase;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    if (preg_match('/^action:(?:([a-z0-9_\\\\]+)::)?([a-z0-9_\\\\]+)(?:\?(.*))?$/i', $routeString, $matches) !== 1)
      throw new InvalidRouteException(tr('Invalid route string for action dispatcher'));
    $route = array(
      'parameters' => array()
    );
    if (isset($matches[3])) {
      $route['parameters'] = Http::decodeQuery($matches[3], false);
      if (!is_array($route['parameters']))
        throw new InvalidRouteException(tr('Invalid JSON parameters in route string'));
    }
    if ($matches[1] != '') {
      $route['controller'] = $matches[1];
      $route['action'] = $matches[2];
    }
    else if (ucfirst($matches[2]) === $matches[2]) {
      $route['controller'] = $matches[2];
    }
    else {
      if (isset($this->m->routing->route['controller']))
        $route['controller'] = $this->m->routing->route['controller'];
      $route['action'] = $matches[2];
    }
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    if (!isset($route['action']))
      $route['action'] = 'index';
    return 'action:' . $route['controller'] . '::' . $route['action'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function isCurrent($route) {
    if (!isset($route['action']))
      $route['action'] = 'index';
    $selection = $this->m->routing->route;
    if (!isset($selection['controller']))
      return false;
    return $selection['controller'] == $route['controller']
      and ($route['action'] == '*'
        or $selection['action'] == $route['action'])
      and ($route['parameters'] == '*'
        or $selection['parameters'] == $route['parameters']);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPath($route, $path = null) {
    if (!isset($path))
      return null;
    return Routing::insertParameters($route['parameters'], $path);
  }

  /**
   * {@inheritdoc}
   */
  public function createDispatch($route) {
    $controller = $this->getController($route['controller']);
    if (!isset($controller))
      throw new InvalidRouteException(tr('Invalid controller: %1', $route['controller']));
    if (!isset($route['action']))
      $route['action'] = 'index';
    if (!$controller->isAction($route['action'])) {
      throw new InvalidRouteException(tr(
        'Invalid action: %1',
        $route['controller'] . '::' . $route['action']
      ));
    }
    return function() use($controller, $route) {
      $controller->before();
      $response = call_user_func_array(array(
        $controller, $route['action']), $route['parameters']
      );
      $controller->after($response);
      return $response;
    };
  }
}
