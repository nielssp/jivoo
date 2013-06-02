<?php
class Route {
  
  const TYPE_AUTO = 1;
  const TYPE_ROOT = 2;
  const TYPE_ERROR = 3;
  const TYPE_MATCH = 4;
  
  private $route;
  private $type;
  
  private $pattern;
  private $priority = 5;
  
  private $only = array();
  private $except = array();
  
  private function __construct($route, $type) {
    $this->route = $route;
    $this->type = $type;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'route':
      case 'type':
        return $this->$property;
    }
  }
  
  public function draw(Routing $routing, Controllers $controllers) {
    $this->route = $routing->validateRoute($this->route);
    switch ($this->type) {
      case self::TYPE_AUTO:
        if (!isset($this->route['controller'])) {
          throw new Exception(tr('Auto routing requires controller'));
        }
        $controller = $controllers->getController($this->route['controller']);
        if (isset($this->route['action'])) {
          $controller->autoRoute($this->route['action']);
        }
        else {
          $controller->autoRoute();
        }
        break;
      case self::TYPE_ROOT:
        $routing->setRoot($this->route);
        break;
      case self::TYPE_ERROR:
        $routing->setError($this->route);
        break;
      case self::TYPE_MATCH:
        break;
    }
  }

  public static function auto($route, $options = array()) {
    $object = new Route($route, self::TYPE_AUTO);
    if (isset($options['except'])) {
      $object->except = $options['except']; 
    }
    if (isset($options['only'])) {
      $object->only = $options['only'];
    }
    return $object;
  }
  
  public static function root($route) {
    $object = new Route($route, self::TYPE_ROOT);
    return $object;
  }
  
  public static function error($route) {
    $object = new Route($route, self::TYPE_ERROR);
    return $object;
  }
  
  public static function match($pattern, $route, $priority = 5) {
    $object = new Route($route, self::TYPE_MATCH);
    $object->pattern = $pattern;
    $object->priority = $priority;
    return $object;
  }
}