<?php
/**
 * Installation and setup system..
 * @package Jivoo\Setup
 */
class Setup extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Controllers', 'Routing', 'View', 'Assets');
  
  /**
   * @var string Name of current setup action.
   */
  private $current = null;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->app->attachEventHandler('afterLoadModule', array($this, 'runSetup'));
  }

  /**
   * Run configured setups.
   * @param LoadModuleEvent $event Event data.
   * @throws Exception If controller not found.
   */
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
          $this->view->addTemplateDir($this->p('templates'));
          $this->m->Routing->followRoute($route);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
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
  
  /**
   * Get state of a setup action.
   * @param array|ILinkable|string|null $route Setup route, see {@see Routing}.
   * @return bool True if setup has finished, false otherwise.
   */
  public function getState($route) {
    $route = $this->m->Routing->validateActionRoute($route);
    $controller = $route['controller'];
    $action = $route['action'];
    $name = $controller . '::' . $action;
    return isset($this->config[$name]) and $this->config[$name] === true;
  }
  
  /**
   * Set state of a setup action.
   * @param array|ILinkable|string|null $route Setup route, see {@see Routing}.
   * @param bool $done Whether or not the setup has finished.
   */
  public function setState($route, $done) {
    $route = $this->m->Routing->validateActionRoute($route);
    $controller = $route['controller'];
    $action = $route['action'];
    $name = $controller . '::' . $action;
    $this->config[$name] = $done;
  }
}
