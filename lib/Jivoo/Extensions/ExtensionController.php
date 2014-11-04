<?php

abstract class ExtensionController extends ExtensionModule {

  protected $modules = array('Helpers', 'Models');
  /**
   * @var string[] A list of other helpers needed by this helper
  */
  protected $helpers = array();
  
  /**
   * @var string[] A list of models needed by this helper
  */
  protected $models = array();
  
  /**
   * @var array An associative array of helper names and objects
  */
  private $helperObjects = array();
  
  /**
   * @var array An associative array of model names and objects
  */
  private $modelObjects = array();

  protected function init() {
    $this->inheritElements('helpers');
    $this->inheritElements('models');
    
    $this->modelObjects = $this->m->Models->getModels($this->models);
    $helperObjects = $this->m->Helpers->getHelpers($this->helpers);
    
    foreach ($helperObjects as $name => $helper) {
      $this->$name = $helper;
    }
  }

  /**
   * @return Response
   */
  public function configure() { }
  
  
  /**
   * Get an associated model, helper or data-value (in that order)
   * @param string $name Name of model/helper or key for data-value
   * @return Model|Helper|mixed Associated value 
   */
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    return $this->view->data->$name;
  }

  /**
   * Set data value, the data is passed along to the template when rendering
   * @param string $name Key
   * @param mixed $value Value
   */
  public function __set($name, $value) {
    $this->view->data->$name = $value;
  }
  
  public function __isset($name) {
    if (isset($this->modelObjects[$name]))
      return true;
    return isset($this->view->data->$name);
  }
  
  public function __unset($name) {
    unset($this->view->data->$name);
  }
  /**
   * Redirect to a route
   * @param array|ILinkable|string|null $route Route, see {@see Routing}
   */
  protected function redirect($route = null) {
    $this->m->Routing->redirect($route);
  }
  
  /**
   * Refresh the current path with optional query data and fragment
   * @param array $query Associative array of query data
   * @param string $fragment Fragment of page
   */
  protected function refresh($query = null, $fragment = null) {
    $this->m->Routing->refresh($query, $fragment);
  }
  
  /**
   * Render a template
   *
   * If $templateName is not set, the path of the template will be computed
   * based on the name of the controller and the name of the action. Each level
   * of inheritance will be an additional directory level. The AppController
   * does not count, and will result in the root of the template directory. An
   * example is {@see AuthenticationSetupController} which inherits from
   * {@see SetupController}. If the action is 'setupRoot', the resulting
   * template path is 'setup/authentication/setup-root.html'.
   * {@see Utilities::camelCaseToDashes()} is used on each level.
   *
   * @param string $templateName Name of template to render
   * @return string The output of $return is set to true
   */
  protected function render($templateName = null) {
    $this->view->addTemplateDir($this->p(''), 4);
    if (!isset($templateName))
      $templateName = Utilities::camelCaseToDashes(get_class($this)) . '.html';
    return new ViewResponse($this->status, $this->view, $templateName);
  }
}
