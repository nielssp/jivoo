<?php
/**
 * Used for configuring routes between paths and actions
 * @package Core\Routing
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
   * @param Controllers $controllers Controllers module
   * @throws Exception if type is auto routing and controller is not set
   */
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
   * Create route for root, i.e. the frontpage
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
}