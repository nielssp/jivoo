<?php
abstract class BaseModel extends BaseObject implements ISelectable {

  public abstract function commit();

  public abstract function delete();
  
  public function json() {
    $array = array();
    foreach ($this->_getters as $property) {
      $array[$property] = $this->$property;
    }
    return json_encode($array);
  }

}

