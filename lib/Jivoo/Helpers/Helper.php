<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\Module;
use Jivoo\Core\App;

/**
 * A helper for use in controllers and templates.
 */
abstract class Helper extends Module {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers', 'Models');

  /**
   * @var string[] A list of other helpers needed by this helper.
   */
  protected $helpers = array();
  
  /**
   * @var string[] A list of models needed by this helper.
   */
  protected $models = array();

  /**
   * @var Helper[] An associative array of helper names and objects.
   */
  private $helperObjects = array();

  /**
   * @var IModel[] An associative array of model names and objects.
   */
  private $modelObjects = array();

  /**
   * Construct helper.
   * @param App $app Associated application.
   */
  public final function __construct(App $app) {
    $this->inheritElements('modules');
    $this->inheritElements('helpers');
    $this->inheritElements('models');
    parent::__construct($app);
    $this->helperObjects = $this->m->Helpers->getHelpers($this->helpers);
    $this->modelObjects = $this->m->Models->getModels($this->models);
    $this->init();
  }

  /**
   * Get a model instance or a helper instance, in that order.
   * @param string $property Name of model or helper (without 'Helper'-suffix).
   * @return Model|Helper|void Model object or helper object.
   */
  public function __get($property) {
    if (isset($this->modelObjects[$property])) {
      return $this->modelObjects[$property];
    }
    if (isset($this->helperObjects[$property])) {
      return $this->helperObjects[$property];
    }
    return parent::__get($property);
  }

  /**
   * Initialisation method called by constructor.
   */
  protected function init() {}
  
  /**
   * Convert a route to a link.
   * @param array|ILinkable|string|null $route Route, see {@see Routing}.
   * @return string A link.
   */
  protected function getLink($route) {
    return $this->m->Routing->getLink($route);
  }

}
