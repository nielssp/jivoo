<?php
// Module
// Name           : Setup
// Description    : The Jivoo installation/setup system.
// Author         : apakoh.dk
// Dependencies   : Jivoo/Controllers Jivoo/Routing Jivoo/Templates Jivoo/Assets

/**
 * Setup module.
 * @package Jivoo\Setup
 */
class Setup extends LoadableModule {
  
  protected $modules = array('Controllers', 'Routing', 'Templates', 'Assets');
  
  private $current = null;

  protected function init() {
    $this->app->attachEventHandler('afterLoadModule', array($this, 'runSetup'));
  }

  public function runSetup(LoadModuleEvent $event) {
    $this->app->detachEventHandler('afterLoadModule', array($this, 'runSetup'));
    if (isset($this->app->appConfig['setup'])) {
      foreach ($this->app->appConfig['setup'] as $route) {
        $route = $this->m->Routing->validateActionRoute($route);
        $controller = $route['controller'];
        $action = $route['action'];
        $name = $controller . '::' . $action;
        if (!isset($this->config[$name]) or $this->config[$name] !== true) {
          $this->current = $name;
          $object = $this->m->Controllers->getController($controller);
          if (!isset($object)) {
            throw new Exception(tr('Controller not found: %1', $controller));
          }
          $object->autoRoute($action);
          $this->m->Routing->reroute($controller, $action);
          $this->m->Routing->followRoute($route);
        }
      }
    }
  }

  public function __get($property) {
    switch ($property) {
      case 'current':
        return $this->$property;
      case 'currentState':
        if (isset($this->current)) {
          return isset($this->config[$this->current]) and
                 $this->config[$this->current] === true;
        }
        return false;
    }
    return parnet::__get($property);
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'currentState':
        if (isset($this->current)) {
          $this->config[$this->current] = $value;
        }
        return;
    }
    return parent::__set($property, $value);
  }
  
  public function getState($route) {
    $route = $this->m->Routing->validateActionRoute($route);
    $controller = $route['controller'];
    $action = $route['action'];
    $name = $controller . '::' . $action;
    return isset($this->config[$name]) and $this->config[$name] === true;
  }
  
  public function setState($route, $done) {
    $route = $this->m->Routing->validateActionRoute($route);
    $controller = $route['controller'];
    $action = $route['action'];
    $name = $controller . '::' . $action;
    $this->config[$name] = $done;
  }
}
