<?php
// Module
// Name           : Routing
// Description    : The Jivoo routing system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Controllers

/**
 * Module for handling routes and HTTP requests.
 * 
 * A "route" as a value is either an array, an {@see ILinkable} object,
 * a string or `null`.
 * 
 * The format of the array is:
 * <code>
 * array(
 *   'url' => ..., // a URL (should be absolute)
 *   'path' => ..., // a path array e.g. array('posts', '23') : /posts/23
 *   'query' => ..., // query array e.g. array('p' => '27') : ?p=27
 *   'mergeQuery' => ..., // boolean, whether to merge with current query
 *   'fragment' => ..., // fragment string e.g. 'bottom' : #bottom
 *   'controller' => ..., // Controller-object or name (w/ or w/o 'Controller'-suffix)
 *   'action' => ..., // name of action as string
 *   'parameters' => ... // array of parameters e.g. array(23, 1)
 * )
 * </code>
 * 
 * The array is validated before use by {@see Routing::validateRoute()}. The
 * following rules are used when converting a route to a link:
 * * If 'url' is set, the url is returned.
 * * If 'controller' isn't set, 'controller', 'action' and 'parameters' default
 *   to their current values.
 * * If 'controller' is set, 'action' defaults to 'index' and 'parameters'
 *   defaults to array().
 * * If 'path' is set, a link based on 'path', 'query' and 'fragment' is
 *   returned.
 * * If 'query' isn't set and 'controller', 'action' and 'parameters' are
 *   left unchanged, the 'query' is set to the current query.
 * 
 * Some examples:
 * * `array()`
 *   A link to the current page.
 * * `array('fragment' => 'test')`
 *   A link to the current page + the fragment #test.
 * * `array('controller' => 'Pages')`
 *   A link to the index-action of the PagesController.
 *   
 * Other legal values are:
 * * An object implementing {@see ILinkable}, in which case the
 *   {@see ILinkable::getRoute()} method is called
 * * `null`, in which case a link to the frontpage is returned
 * * A string, in which case the following grammar applies:
 * <code>
 * route      ::= url
 *             | {namespace "::"} controller ["::" action {"::" parameter}]
 * </code>
 * If the string contains at least one forward slash, it's interpretted as a
 * URL, and returned unchanged. If not it is interpretted as a combination of
 * controller name, action and parameters.
 * Controller namespace and controller names always begin with uppercase
 * characters. An example of a string would be 'Setup::Database::setup', in
 * which 'Setup' is a namespace, 'Database' is the controller and 'setup' is
 * the action. The resulting controller would be 'DatabaseSetupController'.
 * 
 * @package Jivoo\Routing
 */
class Routing extends LoadableModule {
  
  protected $modules = array('Controllers');
  
  /**
   * @var array Selected route and priority
   */
  private $selection = array('route' => null, 'priority' => 0);

  /**
   * @var array Linking paths to routes
   */
  private $paths = array();

  /**
   * @var bool Whether or not the page has rendered yet
   */
  private $rendered = false;
  
  /**
   * @var array Root route and priority
   */
  private $root = array('route' => null, 'priority' => 0);
  
  /**
   * @var mixed Error route
   */
  private $errorRoute = null;

  private $etags = false;
  
  protected $events = array('beforeRender', 'afterRender', 'beforeRedirect', 'beforeCallAction', 'afterCallAction');

  protected function init() {
    // Set default settings
    $this->config->defaults = array(
      'rewrite' => false,
      'sessionPrefix' => $this->app->sessionPrefix,
    );

    $this->request = new Request($this->config['sessionPrefix'], $this->app->basePath);

    // Determine if the current URL is correct
    if ($this->config['rewrite']) {
      if (isset($this->request->path[0]) AND $this->request->path[0] == $this->app->entryScript) {;
        if (count($this->request->path) <= 1) {
          $this->redirectPath(array(), $this->request->query);
        }
        else {
          $this->request->path = array_slice($this->request->path, 1);
          $this->redirectPath($this->request->path, $this->request->query);
        }
      }
    }
    else {
      if (!isset($this->request->path[0]) OR $this->request->path[0] != $this->app->entryScript) {
        $this->redirectPath($this->request->path, $this->request->query);
      }
      $path = $this->request->path;
      array_shift($path);
      $this->request->path = $path;
    }
    
//     $path = explode('/', $this->config['index']['path']);
//     $query = $this->config['index']->get('query', true);
//     if (count($this->request->path) < 1) {
//       $this->request->path = $path;
//       $this->request->query = array_merge($query, $this->request->query);
//     }
//     else if ($path == $this->request->path) {
//       $this->redirectPath(array(), $this->request->query);
//     }

    $routeFile = $this->p('app', 'config/routes.php');
    if (file_exists($routeFile)) {
      $routes = include $routeFile;
      foreach ($routes as $route) {
        if ($route->type == Route::TYPE_ROOT) {
          $this->setRoot($route->route, 9);
        }
        else if ($route->type == Route::TYPE_ERROR) {
          $this->setError($route->route);
        }
        else {
          $route->draw($this);
        }
      }
    }
    
    if (isset($this->config['root'])) {
      $this->setRoot($this->config['root'], 10);
    }

    $this->app->attachEventHandler('afterInit', array($this, 'findRoute'));
  }
  
  /**
   * Set current route
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param number $priority Priority of route
   */
  public function setRoot($route, $priority = 9) {
    $route = $this->validateRoute($route);
    $this->root['route'] = $route;
    if (isset($route['path'])) {
      if (count($this->request->path) < 1) {
        $this->request->path = $route['path'];
        $this->request->query = array_merge($route['query'], $this->request->query);
      }
      else if ($route['path'] == $this->request->path) {
        $this->redirectPath(array(), $this->request->query);
      }
    }
    else {
      if ($priority > $this->root['priority']) {
        if (isset($route['controller'])) {
          $this->addPath(
            $route['controller'],
            $route['action'], 0,
            array($this, 'insertParameters'), array(array()), $priority
          );
        }
        if (count($this->request->path) < 1) {
          $this->setRoute($route, $priority);
        }
      }
    }
  }
  
  /**
   * Set error route
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function setError($route) {
    $this->errorRoute = $route;
    $this->setRoute($route, 1);
  }
  
  /**
   * Get a controller name with or without suffix
   * @param string|Controller $controller Controller name or object
   * @param bool $withSuffix Whether or not to include 'Controller'-suffix
   * @return string Controller name
   */
  protected function controllerName($controller, $withSuffix = false) {
    if (is_object($controller)) {
      $controller = get_class($controller);
    }
    $substr = substr($controller, -10);
    if ($withSuffix AND $substr != 'Controller') {
      return $controller . 'Controller';
    }
    else if (!$withSuffix AND $substr == 'Controller') {
      return substr($controller, 0, -10);
    }
    else {
      return $controller;
    }
  }

  /**
   * Get current request object
   * @return Request Current request
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * Will replace **, :*, * and :n in path with parameters
   * @param mixed[] $parameters Parameters list
   * @param mixed[] $additional Additional parameters, one is used. The first
   * one is used as a path-array (string[])
   * @throws InvalidRouteException if unknown pattern
   * @return mixed[] Resulting path
   */
  public function insertParameters($parameters, $additional) {
    $path = $additional[0];
    $result = array();
    foreach ($path as $part) {
      if ($part == '**' || $part == ':*') {
        while (current($parameters) !== false) {
          $result[] = array_shift($parameters);
        }
        break;
      }
      else if ($part == '*') {
        $part = array_shift($parameters);
      }
      else if ($part[0] == ':') {
        $var = substr($part, 1);
        if (is_numeric($var)) {
          $offset = (int)$var;
          $part = $parameters[$offset];
          unset($parameters[$offset]);
        }
        else if ($var == 'controller') {
          // ???
        }
        else if ($var == 'action') {
          // ???
        }
        else {
          throw new InvalidRouteException(tr(
            'Unknown pattern "%1" in route configuration', $part
          ));
        }
      }
      $result[] = $part;
    }
    return $result;
  }

  /**
   * Get path associated with an action
   * @param string|Controller $controller Controller name or object
   * @param string $action Action name
   * @param mixed[] $parameters Parameters list
   * @throws InvalidRouteException if path was not found
   * @return mixed[] A path
   */
  public function getPath($controller = null, $action = 'index',
                          $parameters = array()) {
    if (!isset($controller)) {
      return null;
    }
    $arity = count($parameters);
    $controller = $this->controllerName($controller);
    $function = null;
    $additional = null;
    if (isset($this->paths[$controller][$action][$arity])) {
      $function = $this->paths[$controller][$action][$arity]['function'];
      $additional = $this->paths[$controller][$action][$arity]['additional'];
    }
    else if (isset($this->paths[$controller][$action]['variadic'])) {
      $function = $this->paths[$controller][$action]['variadic']['function'];
      $additional = $this->paths[$controller][$action]['variadic']['additional'];
    }
    if (!isset($function))
      throw new InvalidRouteException(tr('Could not find path for ' . $controller . '::' . $action . '[' . $arity . ']'));
    $path = call_user_func($function, $parameters, $additional);
    if (!isset($path))
      throw new InvalidRouteException(tr('Could not find path for ' . $controller . '::' . $action . '[' . $arity . ']'));
    return $path;
  }

  /**
   * Check whether or not a route matches the current request
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If route is not valid
   * @return boolean True if current route, false otherwise
   */
  public function isCurrent($route = null, $defaultAction = 'index', $defaultParameters = array()) {
    $route = $this->validateRoute($route, $defaultAction, $defaultParameters);
    if (isset($route['url'])) {
      return false;
    }
    if (isset($route['path'])) {
      if ($route['path'] == array()) {
        if ($this->request->path == array()) {
          return true;
        }
        if (isset($this->root['route']) AND isset($this->root['route']['path'])
          AND $this->request->path == $this->root['route']['path']) {
          return true;
        }
      }
      return $this->request->path == $route['path'];
    }
    if (isset($route['controller']) AND isset($route['action'])) {
      return $this->selection['route']['controller'] == $route['controller']
        AND ($route['action'] == '*'
          OR $this->selection['route']['action'] == $route['action'])
        AND ($route['parameters'] == '*'
          OR $this->selection['route']['parameters'] == $route['parameters']);
    }
    throw new InvalidRouteException(tr('Incomplete route'));
  }
  
  /**
   * Get a URL from a path, query and fragment
   * @param string[] $path Path as array
   * @param array $query GET query
   * @param string $fragment Fragment
   * @param string $rewrite If true 'index.php/' will not be included in link.
   * @return string A URL
   */
  public function getLinkFromPath($path = null, $query = null, $fragment = null,
    $rewrite = false) {
    if (!isset($path)) {
      $path = $this->request
      ->path;
    }
    if (isset($fragment)) {
      $fragment = '#' . $fragment;
    }
    else {
      $fragment = '';
    }
    if (is_array($query) AND count($query) > 0) {
      $queryStrings = array();
      foreach ($query as $key => $value) {
        if ($value == '') {
          $queryStrings[] = urlencode($key);
        }
        else {
          $queryStrings[] = urlencode($key) . '=' . urlencode($value);
        }
      }
      $combined = implode('/', $path) . '?' . implode('&', $queryStrings)
      . $fragment;
      if ($this->config['rewrite'] OR $rewrite) {
        return $this->w($combined);
      }
      else {
        return $this->w($this->app->entryScript . '/' . $combined);
      }
    }
    else {
      if ($this->config['rewrite'] OR $rewrite) {
        return $this->w(implode('/', $path) . $fragment);
      }
      else {
        return $this->w($this->app->entryScript . '/' . implode('/', $path) . $fragment);
      }
    }
  }
  
  public function validateActionRoute($route) {
    $route = $this->validateRoute($route);
    if (!isset($route['controller']) or !isset($route['action']))
      throw new InvalidRouteException(tr('Not a valid action route, must contain controller and action'));
    return $route;
  }
  
  /**
   * Merge two routes
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @param array $mergeWith Route array to merge with
   * @param array Resulting route (as an array)
   */
  public function mergeRoutes($route = null, $mergeWith = array()) {
    $route = $this->validateRoute($route);
    return array_merge($route, $mergeWith);
  }

  /**
   * Validate route
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If invalid route
   * @return array A valid route array
   */
  public function validateRoute($route, $defaultAction = 'index', $defaultParameters = array()) {
    if (!isset($route)) {
      return array('path' => array(), 'query' => array(), 'fragment' => null);
    }
    if (is_string($route)) {
      if (strpos($route, '/') !== false) {
        return array('url' => $route);
      }
      $parts = explode('::', $route);
      $route = array();
      $i = 0;
      while (isset($parts[$i]) and Utilities::isUpper($parts[$i][0])) {
        if (!isset($route['controller'])) {
          $route['controller'] = '';
        }
        $route['controller'] = $parts[$i] . $route['controller'];
        $i++;
      }
      if (isset($parts[$i])) {
        $route['action'] = $parts[$i];
        $route['parameters'] = array();
        for ($i++; $i < count($parts); $i++) {
          $route['parameters'][] = $parts[$i];
        }
      }
    }
    else if (is_object($route) and $route instanceof ILinkable) {
      return $this->validateRoute($route->getRoute());
    }
    if (!is_array($route)) {
      throw new InvalidRouteException(tr('Not a valid route, must be array or string'));
    }
    if (isset($route['url'])) {
      return $route;
    }
    if (!isset($route['query'])){
      $route['query'] = array();
    }
    if (isset($route['mergeQuery']) and $route['mergeQuery'] == true) {
      $route['query'] = array_merge($this->request->query, $route['query']);
    }
    if (!isset($route['fragment'])) {
      $route['fragment'] = null;
    }
    if (isset($route['path'])) {
      return $route;
    }
    $parameters = array();
    foreach ($route as $key => $value) {
      if (is_int($key))
        $parameters[] = $value;
    }
    if (!empty($parameters))
      $route['parameters'] = $parameters;
    if (isset($route['controller'])) {
      $route['controller'] = $this->controllerName($route['controller']);
      if (!isset($route['action']) and isset($defaultAction)) {
        $route['action'] = $defaultAction;
      }
      if (!isset($route['parameters']) and isset($defaultParameters)) {
        $route['parameters'] = $defaultParameters;
      }
    }
    else if (isset($this->selection['route']['controller'])) {
      $route['controller'] = $this->selection['route']['controller'];
      if (!isset($route['action'])) {
        $route['action'] = $this->selection['route']['action'];
      }
      if (!isset($route['parameters'])) {
        $route['parameters'] = $this->selection['route']['parameters'];
      }
    }
    if (isset($route['parameters']) and is_array($route['parameters']))
      $route['parameters'] = array_values($route['parameters']);
    return $route;
  }
  
  /**
   * Get a URL for a route (including http://domain.name)
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException if incomplete route
   * @return string A URL
   */
  public function getUrl($route = null) {
    $route = $this->validateRoute($route);
    if (isset($route['url'])) {
      return $route['url'];
    }
    if (isset($route['path'])) {
      $link = $this->getLinkFromPath($route['path'], $route['query'], $route['fragment']);
    }
    else if (isset($route['controller']) AND isset($route['action'])) {
      $link = $this->getLinkFromPath(
        $this->getPath($route['controller'], $route['action'], $route['parameters']),
        $route['query'],
        $route['fragment']
      );
    }
    else {
      throw new InvalidRouteException(tr('Incomplete route'));
    }
    return $this->request->domainName . $link;
  }
  
  /**
   * Get a link for a route (absolute path)
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException if incomplete route
   * @return string A link
   */
  public function getLink($route = null) {
    $route = $this->validateRoute($route);
    if (isset($route['url'])) {
      return $route['url'];
    }
    if (isset($route['path'])) {
      return $this->getLinkFromPath($route['path'], $route['query'], $route['fragment']);
    }
    if (isset($route['controller']) AND isset($route['action'])) {
      return $this->getLinkFromPath(
        $this->getPath($route['controller'], $route['action'], $route['parameters']),
        $route['query'],
        $route['fragment']
      );
    }
    throw new InvalidRouteException(tr('Incomplete route'));
  }
  
  /**
   * Perform a redirect
   * @param string[] $path Path array
   * @param array $query GET query
   * @param string $moved Whether or not to use a 301 status code
   * @param string $fragment Fragment
   * @param string $rewrite If true 'index.php/' will not be included
   */
  public function redirectPath($path = null, $query = null, $moved = true,
    $fragment = null, $rewrite = false) {
    $status = $moved ? Http::MOVED_PERMANENTLY : Http::SEE_OTHER;
    Http::redirect($status, $this->getLinkFromPath($path, $query, $fragment));
  }

  /**
   * Perform a redirect
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function redirect($route = null) {
    $this->triggerEvent('beforeRedirect', new RedirectEvent($this, $route, false));
    Http::redirect(Http::SEE_OTHER, $this->getLink($route));
  }

  /**
   * Perform a permanent redirect
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function moved($route = null) {
    $this->triggerEvent('beforeRedirect', new RedirectEvent($this, $route, true));
    Http::redirect(Http::MOVED_PERMANENTLY, $this->getLink($route));
  }

  /**
   * Refresh current page
   * @param array $query GET query, default is current
   * @param string $fragment Fragment, default is none
   */
  public function refresh($query = null, $fragment = null) {
    if (!isset($query)) {
      $query = $this->request->query;
    }
    $this->redirectPath($this->request->path, $query, false, $fragment);
    $this->refresh($query, $fragment);
  }
  
  /**
   * Automatically create routes for a controller
   * @param string $controller Controller name, e.g. 'Posts'
   * @param string $action Optional action name
   * @param string $prefix Prefix...
   */
  public function autoRoute($controller, $action = null, $prefix = '') {
    if (isset($action)) {
      $class = $this->m->Controllers->getClass($controller);
      if (!$class) {
        throw new Exception(tr('Invalid controller: %1', $controller));
      }
      $route = array(
        'controller' => $controller,
        'action' => $action
      );
      $reflect = new ReflectionMethod($class, $action);
      $required = $reflect->getNumberOfRequiredParameters();
      $total = $reflect->getNumberOfParameters();
      if (!empty($prefix) AND substr($prefix, -1) != '/') {
        $prefix .= '/';
      }
      $controller = '';
      $paction = '';
      if ($class != 'AppController') {
        $parent = get_parent_class($class);
        while ($parent !== false AND $parent != 'Controller'
          AND $parent != 'AppController') {
          $name = str_replace($parent, '', $class);
          $controller = '/' . $this->m->Controllers->getControllerPath($name) . $controller;
          $class = $parent;
          $parent = get_parent_class($class);
        }
        $name = str_replace('Controller', '', $class);
        $controller = $prefix . $this->m->Controllers->getControllerPath($name) . $controller;
        $paction = '/';
      }
      
      $paction .= Utilities::camelCaseToDashes($action);
      if ($action == 'index') {
        $this->addRoute($controller, $route);
      }
      $path = $controller . $paction;
      if ($required < 1) {
        $this->addRoute($path, $route);
      }
      for ($i = 0; $i < $total; $i++) {
        $path .= '/*';
        if ($i <= $required) {
          $this->addRoute($path, $route);
        }
      }
    }
    else {
      $actions = $this->m->Controllers->getActions($controller);
      if ($actions === false) {
        throw new Exception(tr('Invalid controller: %1', $controller));
      }
      foreach ($actions as $action) {
        $this->autoRoute($controller, $action, $prefix);
      }
    }
  }
  
  /**
   * Add a route/path combination. Set route if pattern matches current
   * path.
   * 
   * A pattern is a path such as 'admin/login'. Different placeholders can be
   * used:
   * * A '\*' can be used instead of a parameter. For instance if the path
   *   'users/view/\*' is pointed at the UsersController and the view-action,
   *   any string can be used in place of the asterisk, and the value will be
   *   used as a parameter for the view-action. Multiple asterisks will be used
   *   as multiple parameters.
   * * A colon ':' followed by a number refers to a specific parameter, starting
   *   from 0. The same example as above with a numbered parameter would be
   *   'users/view/:0'.
   * * The placeholders '**' and ':*' are identical, and results in the rest
   *   of the path being put into parameters.
   * * The placeholder ':controller' will set the controller, and ':action' will
   *   set the action. 
   * @param string $pattern A path pattern
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param number $priority Priority of route
   * @throws InvalidRouteException if unknown placeholder
   */
  public function addRoute($pattern, $route, $priority = 5) {
    $route = $this->validateRoute($route);

    $pattern = explode(' ', $pattern);
    $method = 'ANY';
    if (count($pattern) > 1) {
      $method = strtoupper($pattern[0]);
      $pattern = $pattern[1];
    }
    else {
      $pattern = $pattern[0];
    }
    $pattern = explode('/', $pattern);
    
    $path = $this->request->path;
    $arity = 0;
    foreach ($pattern as $part) {
      if ($part == '**' || $part == ':*') {
        $arity = 'variadic';
        break;
      }
      else if ($part == '*') {
        $arity++;
      }
      else if ($part[0] == ':') {
        $var = substr($part, 1);
        if (is_numeric($var)) {
          $arity++;
        }
      }
    }
    $isMatch = true;
    $patternc = count($pattern);
    if ($method != 'ANY' && $method != $this->request->method) {
      $isMatch = false;
    }
    else if ($patternc < count($path) AND $pattern[$patternc - 1] != '**'
      AND $pattern[$patternc - 1] != ':*') {
      $isMatch = false;
    }
    else {
      foreach ($pattern as $j => $part) {
        if ($part == '**' || $part == ':*') {
          $route['parameters'] = array_merge(
            $route['parameters'],
            array_slice($path, $j)
          );
          $arity = 'variadic';
          break;
        }
        else if (!isset($path[$j])) {
          $isMatch = false;
          break;
        }
        if ($path[$j] == $part) {
          continue;
        }
        if ($part == '*') {
          $route['parameters'][] = $path[$j];
          continue;
        }
        if ($part[0] == ':') {
          $var = substr($part, 1);
          if (is_numeric($var)) {
            $route['parameters'][(int)$var] = $path[$j];
          }
          else if ($var == 'controller') {
            $route['controller'] = Utilities::dashesToCamelCase($path[$j]);
          }
          else if ($var == 'action') {
            $route['action'] = lcfirst(Utilities::dashesToCamelCase($path[$j]));
          }
          else {
            throw new InvalidRouteException(tr('Unknown pattern "%1" in route configuration', $part));
          }
          continue;
        }
        $isMatch = false;
        break;
      }
    }
//     Logger::debug('Add route: ' . implode('/', $pattern) . ' -> ' . $route['controller'] . '::' . $route['action'] . '[' . $arity . ']');
    if ($isMatch) {
      if ($priority > $this->selection['priority']) { // or >= ??
        $this->selection['priority'] = $priority;
        $this->selection['route'] = $route;
      }
    }
    if (isset($route['controller']) AND isset($route['action'])) {
      $this->addPath(
        $route['controller'], $route['action'], $arity,
        array($this, 'insertParameters'), array($pattern), $priority
      );
    }
  }

  /**
   * Add a path for a controller/action combination. This is done automatically
   * with the use of {@see Routing::addRoute()}. The path function provided is
   * called with a parameters-array as its first parameter and $additional as
   * its second parameter. It is used when converting a route to a path. For an
   * example of a path function see {@see Routing::insertParameters()}.
   * @param string|Controller $controller Controller name or object
   * @param string $action Action name
   * @param int|"Variadic" $arity Number of parameters or 'variadic' for arbitrary number
   * @param callback $pathFunction Path function used to compute path
   * @param mixed[] $additional Additional parameters, the second parameter for
   * the path function
   * @param int $priority Priority of path
   * @return boolean True if path function added, false if a path function with
   * a higher priority already exists for that controller and action
   */
  public function addPath($controller, $action, $arity, $pathFunction,
                          $additional = array(), $priority = 5) {
    $controller = $this->controllerName($controller);
    if (!isset($this->paths[$controller])) {
      $this->paths[$controller] = array();
    }
    if (!isset($this->paths[$controller][$action])) {
      $this->paths[$controller][$action] = array();
    }
    if (isset($this->paths[$controller][$action][$arity])) {
      if ($priority <= $this->paths[$controller][$action][$arity]['priority']) {
        return false;
      }
    }
    $this->paths[$controller][$action][$arity] = array(
      'function' => $pathFunction,
      'additional' => $additional,
      'priority' => $priority
    );
    return true;
  }

  /**
   * Set current route
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param int $priority Priority of route
   * @return boolean True if successful, false if a route with higher priority
   * was previously set.
   */
  public function setRoute($route, $priority = 7) {
    if ($this->rendered) {
      return false;
    }
    $route = $this->validateRoute($route);
    if ($priority > $this->selection['priority']) {
      $this->selection['priority'] = $priority;
      $this->selection['route'] = $route;
      return true;
    }
    return false;
  }
  
  /**
   * Create routes as desbribed by the routes configuration file
   */
  public function drawRoutes() {
    foreach ($this->routes as $route) {
      $route->draw($this);
    }
  }
  
  /**
   * Find the best route and execute action
   * @throws ModuleNotLoadedException if Controllers module is missing
   * @throws InvalidRouteException if no route selected or no controller selected
   */
  public function findRoute() {
    $this->triggerEvent('beforeRender');

    if (!isset($this->m->Controllers)) {
      throw new ModuleNotFoundException(tr('Missing module: "%1"', 'Controllers'));
    }
    
    if (!isset($this->selection['route'])) {
      throw new InvalidRouteException(tr('No route selected'));
    }
    
    $route = $this->selection['route'];
    $this->followRoute($route); 
  }

  public function followRoute($route) {
    $route = $this->validateRoute($route);
    if (isset($route['url']) OR isset($route['path'])) {
      $this->redirect($route);
    }
    
    if ($this->request->path != array() AND $this->isCurrent($this->root['route'])) {
      if (!isset($this->root['route']) OR !isset($this->root['route']['path'])
          OR $this->request->path != $this->root['route']['path']) {
        $this->redirectPath(array(), $this->request->query);
      }
    }
    
    if (isset($route['query'])) {
      $this->request->query = array_merge($route['query'], $this->request->query);
    }

    if (isset($route['controller'])) {
      Logger::debug('Select action: ' . $route['controller'] . '::' . $route['action']);
      $this->rendered = true;
      $this->request->route = $route;
      try {
        $response = $this->callAction(
          $route['controller'], $route['action'], $route['parameters']
        );
      }
      catch (ResponseOverrideException $e) {
        $response = $e->getResponse();
      }
      catch (NotFoundException $e) {
        return $this->followroute($this->errorRoute);
      }
      $this->respond($response);
    }
    else {
      throw new InvalidRouteException(tr('No controller selected'));
    }
  }

  public function callAction($controllerName, $action, $parameters = array()) {
    $controller = $this->m->Controllers->getController($controllerName);
    if (!isset($controller))
      throw new InvalidRouteException(tr('Invalid controller: %1', $controllerName));
    if (!is_callable(array($controller, $action))) {
      throw new InvalidRouteException(tr(
        'Invalid action: %1',
        $controllerName . '::' . $action
      ));
    }
    $controller->before();
    
    $this->triggerEvent('beforeCallAction', new CallActionEvent($this, $controller, $action, $parameters));
    $response = call_user_func_array(array($controller, $action), $parameters);
    if (is_string($response))
      $response = new TextResponse(Http::OK, 'text', $response);
    if (!($response instanceof Response)) {
      throw new InvalidResponseException(tr(
        'An invalid response was returned from the action %1',
        $controllerName . '::' . $action
      ));
    }
    $this->triggerEvent('afterCallAction', new CallActionEvent($this, $controller, $action, $parameters, $response));
    $controller->after($response);
    return $response;
  }

  /**
   * Sends a response to the client and stops execution of the applicaton
   * @param Response $response Response object
   */
  public function respond(Response $response) {
    if (headers_sent())
      throw new Exception(tr('Headers already sent'));
    Http::setStatus($response->status);
    Http::setContentType($response->type);
    if (isset($response->modified)) {
      header('Modified: ' . Http::date($response->modified));
    }
    if (isset($response->cache)) {
      $cache = $response->cache;
      if (isset($response->maxAge)) {
        $cache .= ', max-age=' . $response->maxAge;
        header('Expires: ' . Http::date(time() + $response->maxAge));
      }
      header('Pragma: ' . $response->cache);
      header('Cache-Control: ' . $cache);
    }
    else if ($this->etags) {
      $tag = md5($response->body);
      header('ETag: ' . $tag);
      header('Cache-Control: must-revalidate');
      header('Pragma: must-revalidate');
      if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        $tags = explode(',', $_SERVER['HTTP_IF_NONE_MATCH']);
        foreach ($tags as $match) {
          if (trim($match) == $tag) {
            Http::setStatus(Http::NOT_MODIFIED);
            $this->app->stop();
          }
        }
      }
    }
    $body = $response->body;
    if (function_exists('bzcompress') and $this->request->acceptsEncoding('bzip2')) {
      header('Content-Encoding: bzip2');
      echo bzcompress($body);
    }
    else if (function_exists('gzencode') and $this->request->acceptsEncoding('gzip')) {
      header('Content-Encoding: gzip');
      echo gzencode($body);
    }
    else {
      echo $body;
    }
    $this->triggerEvent('afterRender');
    $this->app->stop();
  }

  /**
   * Make sure that the current path matches the controller and action. If not,
   * redirect to the right path.
   * @param string|Controller $controller Controller name or object
   * @param string $action Action name
   * @param mixed[] $parameters Action parameters
   */
  public function reroute($controller, $action, $parameters = array()) {
    $currentPath = $this->request->path;
    $actionPath = $this->getPath($controller, $action, $parameters);
    if ($currentPath != $actionPath AND is_array($actionPath)) {
      $this->redirectPath($actionPath, $this->getRequest()->query);
    }
  }
}

/**
 * Invalid route
 * @package Jivoo\Routing
 */
class InvalidRouteException extends Exception { }

/**
 * Invalid response
 * @package Jivoo\Routing
 */
class InvalidResponseException extends Exception { }

class NotFoundException extends Exception { }

class ResponseOverrideException extends Exception {
  private $response;

  function __construct(Response $response) {
    $this->response = $response;
  }

  function getResponse() {
    return $this->response;
  }
}

class CallActionEvent extends Event {
  public $controller;
  public $action;
  public $response;
  public function __construct($sender, Controller $controller, $action, $parameters, Response $response = null) {
    parent::__construct($sender, $parameters);
    $this->controller = $controller;
    $this->action = $action;
    $this->response = $response;
  }
}

class RedirectEvent extends Event {
  public $route;
  public $moved;
  public function __construct($sender, $route, $moved) {
    parent::__construct($sender);
    $this->route = $route;
    $this->moved = $moved;
  }
}
