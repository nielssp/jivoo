<?php
// Module
// Name           : Models
// Version        : 0.3.14
// Description    : Apakoh Core model system
// Author         : apakoh.dk

/**
 * 
 * @package Core
 * @subpackage Models
 */
class Models extends ModuleBase implements IDictionary {
  
  private $recordClasses = array();
  private $modelClasses = array();
  
  private $modelObjects = array();
  
  protected function init() {
    Lib::addIncludePath($this->p('models', ''));
    $dir = opendir($this->p('models', ''));
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
        $class = $split[0];
        if (strpos($class, 'Model')) {
          $name = str_replace('Model', '', $class);
          $this->modelClasses[$name] = $class;
        }
        else {
          $this->recordClasses[$class] = $class;
        }
      }
    }
    closedir($dir);
  }
  
  public function getRecordClasses() {
    return $this->recordClasses;
  }
  
  public function getModelClasses() {
    return $this->modelClasses;
  }
  
  public function addModels($controller) {
    $models = $controller->getModelList();
    foreach ($models as $name) {
      $model = $this->getModel($name);
      if ($model != null) {
        $controller->addModel($name, $model);
      }
      else {
        Logger::error(tr('Model "%1" not found for %2', $name, get_class($controller)));
      }
    }
  }
  
  public function setModel($name, IModel $model) {
    if (isset($this->modelClasses[$name])) {
      unset($this->modelClasses[$name]);
    }
    if (isset($this->recordClasses[$name])) {
      unset($this->recordClasses[$name]);
    }
    $this->modelObjects[$name] = $model;
  }
  
  public function getModel($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    return null;
  }
  
  public function __get($name) {
      if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    throw new Exception(tr('Model %1 not found', $name));
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
  
  public function isReadOnly() {
    return false;
  }
}