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
  
  private function __construct($route) {
    $this->route = $route;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'route':
      case 'type':
        return $this->$property;
    }
  }
  
  public function draw(Routing $routing, Controllers $controllers) {
    if (is_string($this->route)) {
      $this->route = $routing->stringToRoute($this->route);
    }
  }

  public static function auto($route, $options = array()) {
  
  }
  
  public static function root($route) {
  
  }
  
  public static function error($route) {
  
  }
  
  public static function match($pattern, $route, $priority = 5) {
    
  }
}