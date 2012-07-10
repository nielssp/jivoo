<?php
class BackendItem implements IGroupable, ILinkable {
  private $route = NULL;
  private $label = '';
  private $group = 0;
  private $backend = NULL;

  public function __construct(Backend $backend) {
    $this->backend = $backend;
  }

  public function __get($property) {
    switch ($property) {
      case 'label':
      case 'group':
      case 'route':
        return $this->$property;
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'label':
      case 'group':
      case 'route':
        $this->$property = $value;
    }
  }
  
  public function setup($label, $group = NULL, $route = NULL) {
    $this->label = $label;
    if (isset($group)) {
      $this->group = $group;
    }
    if (isset($route)) {
      $this->route = $route;
    }
    return $this;
  }

  public function autoRoute(ApplicationController $controller, $action) {
    $controller->autoRoute($action, $this->backend->prefix);
    $this->route = array(
        'controller' => $controller,
        'action' => $action
    );
  }

  public function getLabel() {
    return $this->label;
  }

  public function getRoute() {
    return $this->route;
  }

  public function getGroup() {
    return $this->group;
  }
}