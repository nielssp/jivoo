<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\Utilities;
use Jivoo\View\ViewResponse;

/**
 * Base class for extension controllers.
 * @todo Replace with snippet or something
 */
abstract class ExtensionController extends ExtensionModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers', 'Models');

  /**
   * @var string[] A list of helpers required by controller.
  */
  protected $helpers = array();
  
  /**
   * @var string[] A list of models needed by controller.
  */
  protected $models = array();
  
  /**
   * @var Helper[] An associative array of helper names and objects.
  */
  private $helperObjects = array();
  
  /**
   * @var Model[] An associative array of model names and objects.
  */
  private $modelObjects = array();

  /**
   * {@inheritdoc}
   */
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
   * A special action for the configuration of this extension.
   * @return Response A response.
   */
  public function configure() { }
  
  
  /**
   * Get an associated model, helper or data-value (in that order).
   * @param string $name Name of model/helper or key for data-value.
   * @return Model|Helper|mixed Associated value.
   */
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    if (isset($this->view->data->$name))
      return $this->view->data->$name;
    return parent::__get($name);
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
   * Redirect to a route.
   * @param array|Linkable|string|null $route Route, see {@see Routing}.
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
   * Render a template. Will look for template in the extension directory.
   * 
   * @param string $templateName Name of template to render, uses name of class
   * if not specified.
   * @return ViewResponse A view response.
   */
  protected function render($templateName = null) {
    list($key, $path) = $this->info->getKeyPath();
    $this->view->addTemplateDir($key, $path, 4);
    if (!isset($templateName))
      $templateName = Utilities::camelCaseToDashes(get_class($this)) . '.html';
    return $this->view->renderOnly($templateName);
  }
}
