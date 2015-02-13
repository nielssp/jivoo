<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Event;
use Jivoo\Core\Utilities;
use Jivoo\Core\Logger;

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
 * @property-read array|ILinkable|string|null $route The currently selected
 * route, contains the current controller, action and parameters, see {@see Routing}.
 * @property-read DispatcherCollection $dispatchers Collection of dispatchers.
 * @property-read RoutingTable $routes Routing table.
 */
class Routing extends LoadableModule {
  /**
   * @var DispatcherCollection Collection of dispatchers.
   */
  private $dispatchers;
  
  /**
   * @var RoutingTable Table of routes;
   */
  private $routes;
  
  /**
   * @var string[] Paths;
   */
  private $paths;
  
  /**
   * @var array Root route and priority
   */
  private $root = array('priority' => 0);
  
  /**
   * @var mixed Error route
   */
  private $errorRoute = null;

  /**
   * @var array Selected route and priority
   */
  private $selection = null;

  /**
   * @var bool Whether or not the page has rendered yet
   */
  private $rendered = false;

  /**
   * @var bool Use etags.
   */
  private $etags = false;

  /**
   * {@inheritdoc}
   */
  protected $events = array('beforeRender', 'afterRender', 'beforeRedirect', 'beforeCallAction', 'afterCallAction');

  /**
   * {@inheritdoc}
   */
  protected function init() {
    // Set default settings
    $this->config->defaults = array(
      'rewrite' => false,
      'sessionPrefix' => $this->app->sessionPrefix,
    );

    $this->request = new Request($this->config['sessionPrefix'], $this->app->basePath);
    
    $this->dispatchers = new DispatcherCollection($this);
    $this->dispatchers->add(new PathDispatcher($this));
    $this->dispatchers->add(new UrlDispatcher($this));
    
    $this->routes = new RoutingTable($this);

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

    if (isset($this->config['root'])) {
      $this->setRoot($this->config['root'], 10);
    }

    $routesFile = $this->p('app', 'config/routes.php');
    if (file_exists($routesFile)) {
      $routes = $this->routes;
      $this->app->attachEventHandler(
        'afterLoadModules',
        function() use($routes, $routesFile) {
          $routes->load($routesFile);
        }
      );
    }
    $this->app->attachEventHandler('afterInit', array($this, 'findRoute'));
  }
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'dispatchers':
      case 'routes':
        return $this->$property;
      case 'route':
        return $this->selection;
    }
    return parent::__get($property);
  }
  
  /**
   * Set current route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param number $priority Priority of route.
   */
  public function setRoot($route, $priority = 9) {
    if ($priority <= $this->root['priority'])
      return;
    $route = $this->validateRoute($route);
    $route['priority'] = $priority;
    $this->root = $route;
    if (isset($route['path'])) {
      if (count($this->request->path) === 0) {
        $this->request->path = $route['path'];
        $this->request->query = array_merge($route['query'], $this->request->query);
      }
      else if ($route['path'] == $this->request->path) {
        $this->redirectPath(array(), $this->request->query);
      }
    }
    else {
      $this->addPath($route, array(), 0, $priority);
      if (count($this->request->path) === 0) {
        $this->setRoute($route, $priority);
      }
    }
  }
  
  /**
   * Set error route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function setError($route) {
    $this->errorRoute = $route;
    $this->setRoute($route, 1);
  }

  /**
   * Will replace **, :*, * and :n in path with parameters.
   * @param mixed[] $parameters Parameters list.
   * @param mixed[] $additional Additional parameters, one is used. The first
   * one is used as a path-array (string[]).
   * @throws InvalidRouteException If unknown pattern.
   * @return mixed[] Resulting path.
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
   * Check whether or not a route matches the current request.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If route is not valid.
   * @return boolean True if current route, false otherwise.
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
        if (isset($this->root['route']) and isset($this->root['route']['path'])
          AND $this->request->path == $this->root['route']['path']) {
          return true;
        }
      }
      return $this->request->path == $route['path'];
    }
    if (isset($route['controller']) and isset($route['action'])) {
      return $this->selection['route']['controller'] == $route['controller']
        and ($route['action'] == '*'
          or $this->selection['route']['action'] == $route['action'])
        and ($route['parameters'] == '*'
          or $this->selection['route']['parameters'] == $route['parameters']);
    }
    throw new InvalidRouteException(tr('Incomplete route'));
  }
  
  /**
   * Get a URL from a path, query and fragment.
   * @param string[] $path Path as array.
   * @param array $query GET query.
   * @param string $fragment Fragment.
   * @param string $rewrite If true 'index.php/' will not be included in link.
   * @return string A URL.
   */
  public function getLinkFromPath($path = null, $query = null, $fragment = null,
    $rewrite = false) {
    if (!isset($path)) {
      $path = $this->request->path;
    }
    if (isset($fragment)) {
      $fragment = '#' . $fragment;
    }
    else {
      $fragment = '';
    }
    if (is_array($query) and count($query) > 0) {
      $queryStrings = array();
      foreach ($query as $key => $value) {
        if ($value === '') {
          $queryStrings[] = urlencode($key);
        }
        else {
          $queryStrings[] = urlencode($key) . '=' . urlencode($value);
        }
      }
      $combined = implode('/', $path) . '?' . implode('&', $queryStrings) .
                   $fragment;
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
   * Merge two routes.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param array $mergeWith Route array to merge with.
   * @param array Resulting route (as an array).
   */
  public function mergeRoutes($route = null, $mergeWith = array()) {
    $route = $this->validateRoute($route);
    return array_merge($route, $mergeWith);
  }

  /**
   * Validate route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If invalid route.
   * @return array A valid route array.
   */
  public function validateRoute($route) {
    return $this->dispatchers->validate($route);
  }
  
  /**
   * Get a URL for a route (including http://domain.name).
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If incomplete route.
   * @return string A URL.
   */
  public function getUrl($route = null) {
    $link = $this->getLink($route);
    if (strpos($link, '://') !== false)
      return $link;
    return $this->request->domainName . $link;
  }
  
  /**
   * Get a link for a route (absolute path).
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If incomplete route.
   * @return string A link.
   */
  public function getLink($route = null) {
    $route = $this->validateRoute($route);
    $arity = '(' . count($route['parameters']) . ')';
    $routeString = $route['dispatcher']->fromRoute($route);
    $path = null;
    if (isset($this->paths[$routeString . $arity])) {
      $path = $route['dispatcher']->getPath($this->paths[$routeString . $arity]['pattern'], $route);
    }
    else if (isset($this->paths[$routeString . '[*]'])) {
      $path = $route['dispatcher']->getPath($this->paths[$routeString . '(*)']['pattern'], $route);
    }
    $path = $route['dispatcher']->getPath($route, $path);
    if (!isset($path))
      throw new InvalidRouteException(tr('Could not find path for ' . $routeString . $arity));
    if (is_string($path))
      return $path;
    return $this->getLinkFromPath($path, $route['query'], $route['fragment']);
  }
  
  /**
   * Perform a redirect.
   * @param string[] $path Path array.
   * @param array $query GET query.
   * @param string $moved Whether or not to use a 301 status code.
   * @param string $fragment Fragment.
   * @param string $rewrite If true 'index.php/' will not be included.
   */
  public function redirectPath($path = null, $query = null, $moved = true,
    $fragment = null, $rewrite = false) {
    $status = $moved ? Http::MOVED_PERMANENTLY : Http::SEE_OTHER;
    Http::redirect($status, $this->getLinkFromPath($path, $query, $fragment));
  }

  /**
   * Perform a redirect.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function redirect($route = null) {
    $this->triggerEvent('beforeRedirect', new RedirectEvent($this, $route, false));
    Http::redirect(Http::SEE_OTHER, $this->getLink($route));
  }

  /**
   * Perform a permanent redirect.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function moved($route = null) {
    $this->triggerEvent('beforeRedirect', new RedirectEvent($this, $route, true));
    Http::redirect(Http::MOVED_PERMANENTLY, $this->getLink($route));
  }

  /**
   * Refresh current page.
   * @param array $query GET query, default is current.
   * @param string $fragment Fragment, default is none.
   */
  public function refresh($query = null, $fragment = null) {
    if (!isset($query)) {
      $query = $this->request->query;
    }
    $this->redirectPath($this->request->path, $query, false, $fragment);
    $this->refresh($query, $fragment);
  }
  
  /**
   * Automatically create routes for a controller.
   * @param string $controller Controller name, e.g. 'Posts'.
   * @param string $action Optional action name.
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
      $reflect = new \ReflectionMethod($class, $action);
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
        throw new \Exception(tr('Invalid controller: %1', $controller));
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
   *
   * @param string $pattern A path pattern.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param number $priority Priority of route.
   * @throws InvalidRouteException If unknown placeholder.
   */
  public function addRoute($pattern, $route, $priority = 5) {
    $route = $this->validateRoute($route);
    $route['priority'] = $priority;

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
        $arity = '*';
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
          $arity = '*';
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
          else {
            $route['parameters'][$var] = $path[$j];
          }
          continue;
        }
        $isMatch = false;
        break;
      }
    }
    if ($isMatch) {
      if ($priority > $this->selection['priority']) { // or >= ??
        $this->selection = $route;
      }
    }
    $this->addPath($route, $pattern, $arity, $priority);
  }

  /**
   * Add association of route and path-pattern.
   * 
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param string[] $pattern A pattern array.
   * @param int|string $arity Arity of pattern (integer or '*').
   * @param int $priority Priority of path.
   * @return bool True if pattern added, false otherwise.
   */
  public function addPath($route, $pattern, $arity, $priority = 5) {
    $route = $this->validateRoute($route);
    $key = $route['dispatcher']->fromRoute($route) . '(' . $arity . ')';
    if (isset($this->paths[$key])) {
      if ($priority <= $this->paths[$key]['priority'])
        return false;
    }
    $this->paths[$key] = array(
      'pattern' => $pattern,
      'priority' => $priority
    );
    return true;
  }

  /**
   * Set current route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param int $priority Priority of route.
   * @return boolean True if successful, false if a route with higher priority
   * was previously set.
   */
  public function setRoute($route, $priority = 7) {
    if ($this->rendered) {
      return false;
    }
    $route = $this->validateRoute($route);
    if (!isset($this->selection) or $priority > $this->selection['priority']) {
      $this->selection = $route;
      $this->selection['priority'] = $priority;
      return true;
    }
    return false;
  }
  
  /**
   * Find the best route and execute action.
   * @throws ModuleNotLoadedException If Controllers module is missing.
   * @throws InvalidRouteException If no route selected or no controller selected.
   */
  public function findRoute() {
    $this->triggerEvent('beforeRender');

    
    if (!isset($this->selection)) {
      throw new InvalidRouteException(tr('No route selected'));
    }
    
    $this->followRoute($this->selection); 
  }

  /**
   * Follow a route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If route is invalid.
   */
  public function followRoute($route) {
    $route = $this->validateRoute($route);
    
    if ($this->request->path != array() and $this->isCurrent($this->root)) {
      $this->redirectPath(array(), $this->request->query);
    }
    
    if (isset($route['query'])) {
      $this->request->query = array_merge($route['query'], $this->request->query);
    }
    
    $this->selection = $route;
    
    $this->rendered = true;
    $this->request->route = $route;
    try {
      $response = $route['dispatcher']->dispatch($route);
      
      if (is_string($response))
        $response = new TextResponse(Http::OK, 'text', $response);
      if (!($response instanceof Response)) {
        throw new InvalidResponseException(tr(
          'An invalid response was returned'
        ));
      }
    }
    catch (ResponseOverrideException $e) {
      $response = $e->getResponse();
    }
    catch (NotFoundException $e) {
      return $this->followRoute($this->errorRoute);
    }
    $this->respond($response);
  }

  /**
   * Sends a response to the client and stops execution of the applicaton.
   * @param Response $response Response object.
   */
  public function respond(Response $response) {
    if (headers_sent($file, $line))
      throw new Exception(tr('Headers already sent in %1 on line %2', $file, $line));
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
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function reroute($route = null) {
    $currentPath = $this->request->path;
    $path = $this->getLink($route);
    if ($currentPath != $actionPath AND is_array($actionPath)) {
      $this->redirectPath($actionPath, $this->request->query);
    }
  }
}

/**
 * Invalid route.
 */
class InvalidRouteException extends \Exception { }

/**
 * Invalid response.
 */
class InvalidResponseException extends \Exception { }

/**
 * Can be used in an action to send the client to the error page.
 */
class NotFoundException extends \Exception { }

/**
 * When thrown, the current response is replaced.
 */
class ResponseOverrideException extends \Exception {
  /**
   * @var Response New response object.
   */
  private $response;

  /**
   * Construct response override.
   * @param Response $response New response object.
   */
  function __construct(Response $response) {
    $this->response = $response;
  }

  /**
   * Get the response object.
   * @return Response Response object.
   */
  function getResponse() {
    return $this->response;
  }
}

/**
 * The event of calling an action.
 */
class CallActionEvent extends Event {
  /**
   * @var Controller The controller.
   */
  public $controller;
  
  /**
   * @var string The name of the action.
   */
  public $action;
  
  /**
   * @var Response|null Response returned by action if any.
   */
  public $response;
  
  /**
   * Construct call action event.
   * @param object $sender Sender object.
   * @param Controller $controller The controller.
   * @param string $action Name of the action.
   * @param string[] $parameters Parameters for action.
   * @param Response|null $response Response returned by action if any.
   */
  public function __construct($sender, Controller $controller, $action, $parameters, Response $response = null) {
    parent::__construct($sender, $parameters);
    $this->controller = $controller;
    $this->action = $action;
    $this->response = $response;
  }
}

/**
 * A redirect event.
 */
class RedirectEvent extends Event {
  /**
   * @var array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public $route;
  
  /**
   * @var bool Whether it is a permanent (true) or temporary (false) redirect.
   */
  public $moved;
  
  /**
   * Construct redirect event.
   * @param object $sender Sender object.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param bool $movied Whether it is a permanent (true) or temporary (false) redirect.
   */
  public function __construct($sender, $route, $moved) {
    parent::__construct($sender);
    $this->route = $route;
    $this->moved = $moved;
  }
}
