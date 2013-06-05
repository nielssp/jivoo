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
class Models extends ModuleBase {
  
  private $models = array();
  private $records = array();
  
  private $modelObjects = array();
  
  protected function init() {
    Lib::addIncludePath($this->p('models', ''));
    $dir = opendir($this->p('models', ''));
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
        $class = $split[0];
//         if (is_subclass_of($class, 'IModel')) {
//           $name = str_replace('Model', '', $class);
//           $this->models[$name] = $class;
//         }
//         else if (is_subclass_of($class, 'IRecord')) {
//           $this->records[$class] = $class;
//         }
      }
    }
    closedir($dir);
  }
  
  public function getModelClasses() {
    return $this->models;
  }
  
  public function getRecordClasses() {
    return $this->records;
  }
  
  public function setModel($name, IModel $model) {
    $this->modelObjects[$name] = $model;
  }
  
  public function getModel($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    return null;
  }
  
  public function __get($name) {
    return $this->getModel($name);
  }
}