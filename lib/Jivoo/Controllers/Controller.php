<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

use Jivoo\Core\Module;
use Jivoo\Core\App;
use Jivoo\Core\Utilities;
use Jivoo\View\ViewResponse;
use Jivoo\Routing\Response;
use Jivoo\Core\Lib;

/**
 * A controller, the C of MVC.
 */
class Controller extends Module {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers', 'Models');
  /**
   * @var string[] A list of other helpers needed by this module.
  */
  protected $helpers = array('Html');
  
  /**
   * @var string[] A list of models needed by this module.
  */
  protected $models = array();
  
  /**
   * @var Helper[] An associative array of helper names and objects.
  */
  private $helperObjects = array();
  
  /**
   * @var IBasicModel[] An associative array of model names and objects.
  */
  private $modelObjects = array();
  
  /**
   * @var string Name of controller (without 'Controller'-suffix).
   */
  private $name;

  /**
   * @var string[] List of actions.
   */
  private $actions = array();

  /**
   * @var HTTP status code.
   */
  private $status = 200;
  
  /**
   * Construct controller.
   * @param App $app Associated application.
   */
  public final function __construct(App $app) {
    $this->inheritElements('modules');
    $this->inheritElements('helpers');
    $this->inheritElements('models');
    parent::__construct($app);
    $this->modelObjects = $this->m->Models->getModels($this->models);
    $helperObjects = $this->m->Helpers->getHelpers($this->helpers);

    foreach ($helperObjects as $name => $helper) {
      $this->$name = $helper;
    }
    
    $this->name = preg_replace('/Controller$/', '', Lib::getClassName($this));

    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);
    
    $this->init();
  }

  /**
   * Get an associated model, helper or data-value (in that order).
   * @param string $name Name of model/helper or key for data-value.
   * @return Model|Helper|mixed Associated value.
   */
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    return $this->view->data->$name;
  }

  /**
   * Set data value, the data is passed along to the template when rendering.
   * @param string $name Key.
   * @param mixed $value Value.
   */
  public function __set($name, $value) {
    $this->view->data->$name = $value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function __isset($name) {
    if (isset($this->modelObjects[$name]))
      return true;
    return isset($this->view->data->$name);
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($name) {
    unset($this->view->data->$name);
  }
  
  /**
   * Get name of controller (without "Controller"-suffix).
   * @return string Name of controller.
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Automatically route a single or all actions in this controller.
   * @param string $action If set, the name of the single action to auto route.
   * @param string $prefix A prefix to use for all resulting paths.
   */
  public function autoRoute($action = null) {
    $this->m->Routing->autoRoute(array(
      'controller' => $this->name,
      'action' => $action
    ));
  }

  /**
   * Add a custom route to an action.
   * @param string $path Path that should lead to action.
   * @param string $action Action name.
   * @param int $priority Priority of route.
   */
  public function addRoute($path, $action, $priority = 5) {
    $this->m->Routing->addRoute(
      $path,
      array('controller' => $this->name, 'action' => $action),
      $priority
    );
  }

  /**
   * Set the current route.
   * @param stirng $action Action name.
   * @param int $priority Priority of route.
   * @param mixed[] $parameters Array of additional parameters for action.
   */
  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routing->setRoute(
      array(
        'controller' => $this->name,
        'action' => $action,
        'parameters' => $parameters
      ), $priority
    );
  }

  /**
   * Ensure that the current path matches the current action, redirect to
   * the correct path if not.
   */
  protected function reroute() {
    list(, $caller) = debug_backtrace(false);
    $this->m->Routing->reroute(array(
      'controller' => $this->name,
      'action' => $caller['function'],
      'parameters' => $caller['args']
    ));
  }

  /**
   * Set return path.
   * @see Controller::goBack()
   */
  protected function returnToThis() {
    //list( , $caller) = debug_backtrace(false);
    //    $this->session['returnTo'] = array('url' => $this->request->url);
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
  }

  /**
   * Return to a previously set return path.
   * @see Controller::returnToThis().
   * @return false False if no return path set.
   */
  protected function goBack() {
    if (!isset($this->session['returnTo'])) {
      return false;
    }
    unset($this->session['returnTo']);
    $this->redirect($this->session['returnTo']);
  }

  /**
   * Call another action in another controller in order.
   * @param string $controller Controller name.
   * @param string $action Action name.
   * @param mixed[] $parameters Parameters.
   * @return Response Response.
   */
  protected function embed($controller, $action, $parameters = array()) {
    $dispatch = $this->m->Routing->dispatchers->action->createDispatch(array(
      'controller' => $controller,
      'action' => $action,
      'parameters' => $parameters
    ));
    $response = $dispatch();
  }

  /**
   * Redirect to a route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  protected function redirect($route = null) {
    $this->m->Routing->redirect($route);
  }

  /**
   * Refresh the current path with optional query data and fragment.
   * @param array $query Associative array of query data.
   * @param string $fragment Fragment of page.
   */
  protected function refresh($query = null, $fragment = null) {
    $this->m->Routing->refresh($query, $fragment);
  }

  /**
   * Set HTTP status code, e.g. 200 for OK or 404 for file not found.
   * @param integer $httpStatus HTTP status code.
   */
  protected function setStatus($httpStatus) {
    $this->status = $httpStatus;
  }
  
  /**
   * Render a template.
   * 
   * If $templateName is not set, the path of the template will be computed
   * based on the name of the controller and the name of the action.
   * 
   * @param string $templateName Name of template to render.
   * @return ViewResponse A view response for template.
   */
  protected function render($templateName = null) {
    if (!isset($templateName)) {
      list(, $caller) = debug_backtrace(false);
      $class = str_replace($this->app->n('Controllers\\'), '', $caller['class']);
      $class = preg_replace('/Controller$/', '', $class);
      $templateName = '';
      if ($class != 'App') {
        $dirs = array_map(array('Jivoo\Core\Utilities', 'camelCaseToDashes'), explode('\\', $class));
        $templateName = implode('/', $dirs) . '/';
      }
      $templateName .= Utilities::camelCaseToDashes($caller['function'])
        . '.html';
    }
    return new ViewResponse($this->status, $this->view, $templateName);
  }

  /**
   * Controller initialization, called by constructor.
   */
  protected function init() {
  }
  
  /**
   * Called just before the selected action is called, useful for doing common
   * tasks for all actions.
   */
  public function before() {
  }
  
  /**
   * Called right after the selected action is called.
   * @param mixed $response Respone object, as returned by action.
   */
  public function after($response) {
  }

}
