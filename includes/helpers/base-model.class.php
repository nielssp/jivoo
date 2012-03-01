<?php

abstract class BaseModel extends BaseObject implements ISelectable {
    
  public static $cache = array();
  
  protected function __construct() {
    
  }
  public abstract function commit();

  public abstract function delete();
  
  public function addToCache() {
    $this::$cache[$this->id] = $this;
  } 
  
  
  public function json() {
    $array = array();
    foreach ($this->_getters as $property) {
      $array[$property] = $this->$property;
    }
    return json_encode($array);
  }

}

