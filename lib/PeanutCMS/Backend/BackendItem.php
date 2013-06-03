<?php
class BackendItem implements IGroupable, ILinkable {
  private $route = null;
  private $label = '';
  private $group = 0;
  private $backend = null;
  private $auth = null;
  private $permission = 'backend.access';
  private $access = null;

  public function __construct(Backend $backend, Authentication $authentication) {
    $this->backend = $backend;
    $this->auth = $authentication;
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

  public function setup($label, $group = null, $route = null) {
    $this->label = $label;
    if (isset($group)) {
      $this->group = $group;
    }
    if (isset($route)) {
      $this->route = $route;
    }
    return $this;
  }

  public function permission($key = null) {
    $this->permission = $key;
    return $this;
  }

  public function hasAccess() {
    if (!isset($this->access)) {
      $this->access = $this->auth->hasPermission($this->permission);
    }
    return $this->access;
  }

  public function autoRoute(Controller $controller, $action) {
    $controller->autoRoute($action, $this->backend->prefix);
    $this->route = array('controller' => $controller, 'action' => $action);
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
