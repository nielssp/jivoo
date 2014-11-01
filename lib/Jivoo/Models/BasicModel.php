<?php
class BasicModel implements IBasicModel {

  private $labels = array();
  private $types = array();
  private $required = array();
  private $name;
  
  public function __construct($name) {
    $this->name = $name;
  }

  protected function addField($field, $label, DataType $type) {
    $this->labels[$field] = $label;
    $this->types[$field] = $type;
    if (!$type->null)
      $this->required[$field] = true;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getFields() {
    return array_keys($this->labels);
  }
  
  public function getLabel($field) {
    if (isset($this->labels[$field]))
      return $this->labels[$field];
    return null;
  }
  
  public function getType($field) {
    if (isset($this->types[$field]))
      return $this->types[$field];
    return null;
  }
  
  public function hasField($field) {
    return isset($this->labels[$field]);
  }
  
  public function isRequired($field) {
    return isset($this->required[$field]);
  }
}