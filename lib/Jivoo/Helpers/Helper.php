<?php
/**
 * A helper for use in controllers and views
 * @package Jivoo\Helpers
 */
abstract class Helper extends Module {
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

  /**
   * Constructor.
   * @param Routing $routing Routing module
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
   * @param string $property Name of model or helper (without 'Helper'-suffix)
   * @return Model|Helper|void Model object or helper object
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
   * Convert a route to a link
   * @param array|ILinkable|string|null $route Route, see {@see Routing}
   * @return string A link
   */
  protected function getLink($route) {
    return $this->m->Routing->getLink($route);
  }

}
