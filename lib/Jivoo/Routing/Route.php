<?php
/**
 * Used for configuring routes between paths and actions
 * @package Jivoo\Routing
 * @property-read array|ILinkable|string|null $route A route, see {@see Routing}
 * @property-read int $type Type of routing
 */
class Route {
  
  /**
   * @var int Auto routing
   */
  const TYPE_AUTO = 1;
  
  /**
   * @var int Routing root (frontpage)
   */
  const TYPE_ROOT = 2;
  
  /**
   * @var int Routing error page
   */
  const TYPE_ERROR = 3;
  
  /**
   * @var int Match a path
   */
  const TYPE_MATCH = 4;
  
  /**
   * @var int Resource routes
   */
  const TYPE_RESOURCE = 5;
  
  /**
   * @var mixed A route
   */
  private $route;
  
  /**
   * @var int Type of route
   */
  private $type;
  
  /**
   * @var Path pattern for matching
   */
  private $pattern;
  
  /**
   * @var int Priority of route
   */
  private $priority = 5;
  
  /**
   * @var string Auto route only these actions
   */
  private $only = array();
  
  /**
   * @var string Don't auto route these actions
   */
  private $except = array();
  
  /**
   * Constructor.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @param int $type Type of route
   */
  private function __construct($route, $type) {
    $this->route = $route;
    $this->type = $type;
  }
  
  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'route':
      case 'type':
        return $this->$property;
    }
  }
  
  /**
   * Make route
   * @param Routing $routing Routing module 
   * @throws Exception if type is auto routing and controller is not set
   */
  public function draw(Routing $routing) {
    switch ($this->type) {
      case self::TYPE_AUTO:
        $this->route = $routing->validateRoute($this->route, null);
        if (!isset($this->route['controller'])) {
          throw new Exception(tr('Auto routing requires controller'));
        }
        if (isset($this->route['action'])) {
          $routing->autoRoute($this->route['controller'], $this->route['action']);
        }
        else {
          $routing->autoRoute($this->route['controller']);
        }
        break;
      case self::TYPE_RESOURCE:
        $this->route = $routing->validateRoute($this->route);
        if (!isset($this->route['controller'])) {
          throw new Exception(tr('Resource routing requires controller'));
        }
        $controller = $this->route['controller'];
        $split = explode('-', Utilities::camelCaseToDashes($controller));
        $patternBase = implode('/', array_reverse($split));
        $routing->addroute($patternBase, $controller . '::index');
        $routing->addroute($patternBase . '/add', $controller . '::add'); //C
        $routing->addroute($patternBase . '/:0', $controller . '::view'); //R
        $routing->addroute($patternBase . '/:0/edit', $controller . '::edit'); //U
        $routing->addroute($patternBase . '/:0/delete', $controller . '::delete'); //D

        $routing->addroute('DELETE ' . $patternBase . '/:0', $controller . '::delete');
        $routing->addroute('PATCH ' . $patternBase . '/:0', $controller . '::edit');
        $routing->addroute('PUT ' . $patternBase . '/:0', $controller . '::edit');
        $routing->addroute('POST ' . $patternBase, $controller . '::add');
        break;
      case self::TYPE_ROOT:
        $routing->setRoot($this->route);
        break;
      case self::TYPE_ERROR:
        $routing->setError($this->route);
        break;
      case self::TYPE_MATCH:
        $routing->addRoute($this->pattern, $this->route, $this->priority);
        break;
    }
  }

  /**
   * Automatically create routes for all actions in a controller or just a
   * single action
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @param array $options An associative array of options for auto routing
   * @return Route Route object
   */
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
  
  /**
   * Create route for root, i.e. the frontpage
   * @param array|ILinkable|string|null $route A route, {@see Routing}
   * @return Route Route object
   */
  public static function root($route) {
    $object = new Route($route, self::TYPE_ROOT);
    return $object;
  }
  
  /**
   * Create route for error page
   * @param array|ILinkable|string|null $route A route, {@see Routing}
   * @return Route Route object
   */
  public static function error($route) {
    $object = new Route($route, self::TYPE_ERROR);
    return $object;
  }


  /**
   * Create route for requests matching a pattern
   * @param string $pattern A path to match, see {@see Routing::addRoute}
   * @param array|ILinkable|string|null $route A route, {@see Routing}
   * @param int $priority Priority of route
   * @return Route Route object
   */
  public static function match($pattern, $route, $priority = 5) {
    $object = new Route($route, self::TYPE_MATCH);
    $object->pattern = $pattern;
    $object->priority = $priority;
    return $object;
  }
  
  /**
   * Automatically create routes for a resource. Expects controller to be set in
   * the route.
   * @param array|ILinkable|string|null $route A route, {@see Routing}
   * @return Route Route object
   */
  public static function resource($route) {
    $object = new Route($route, self::TYPE_RESOURCE);
    return $object;
  }
}