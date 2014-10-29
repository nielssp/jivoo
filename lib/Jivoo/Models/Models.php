<?php
// Module
// Name           : Models
// Description    : Jivoo model system
// Author         : apakoh.dk

Lib::import('Jivoo/Models/Validation');
Lib::import('Jivoo/Models/Selection');
Lib::import('Jivoo/Models/Condition');
Lib::import('Jivoo/Models/File');

/**
 * Models module, finds all models in application
 * @package Jivoo\Models
 */
class Models extends LoadableModule {
  
    /**
   * @var string[] List of model class names
   */
  private $modelClasses = array();
  
  /**
   * @var array Associative array of model names and objects
   */
  private $modelObjects = array();
  
  protected function init() {
    $modelsDir = $this->p('app', 'models');
    if (is_dir($modelsDir)) {
      Lib::addIncludePath($modelsDir);
      $files = scandir($modelsDir);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) AND $split[1] == 'php') {
            $this->addClass($split[0]);
          }
        }
      }
    }
  }
  
  /**
   * Get list of model classes
   * @return string[] List of models
   */
  public function getModelClasses() {
    return $this->modelClasses;
  }
  
  /**
   * Add an IModel class
   * @param string $class Class name
   */
  public function addClass($class) {
    $this->modelClasses[$class] = $class;
  }
  
  /** 
   * Add models needed by a controller or helper
   * @param Controller|Helper $controller Controller or helper objects
   */
  public function addModels($controller) {
    $models = $controller->getModelList();
    foreach ($models as $name) {
      $model = $this->getModel($name);
      if ($model != null) {
        $controller->addModel($name, $model);
      }
      else {
        throw new ModelNotFoundException(tr(
          'Model "%1" not found for %2', $name, get_class($controller)
        ));
      }
    }
  }
  
  /**
   * Add/set model
   * @param string $name Model name
   * @param IModel $model Model object
   */
  public function setModel($name, IModel $model) {
    if (isset($this->modelClasses[$name])) {
      unset($this->modelClasses[$name]);
    }
    $this->modelObjects[$name] = $model;
  }
  
  /**
   * Get a model
   * @param string $name Model name
   * @return IModel|null Model object or null if not found
   */
  public function getModel($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    return null;
  }
  
  public function getModels($names) {
    $models = array();
    foreach ($names as $name)
      $models[$name] = $this->getModel($name);
    return $models;
  }
  
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    throw new ModelNotfoundException(tr('Model "%1" not found', $name));
  }
  
  public function __set($name, $model) {
    $this->setModel($name, $model);
  }
  
  public function __isset($name) {
    return isset($this->modelObjects[$name]);
  }
  
  public function __unset($name) {
    unset($this->modelObjects[$name]);
  }
}

class ModelNotFoundException extends Exception { }
