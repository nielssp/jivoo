<?php
class ViewData implements ArrayAccess {
  
  private $data = array();
  private $templateData = array();
  
  public function __get($property) {
    return $this->data[$property];
  }
  
  public function __set($property, $value) {
    $this->data[$property] = $value;
  }
  
  public function __unset($property) {
    unset($this->data[$property]);
  }
  
  public function __isset($property) {
    return isset($this->data[$property]);
  }
  
  public function toArray() {
    return $this->data;
  }
  
  public function offsetExists($template) {
    return true;
  }

  public function offsetGet($template) {
    if (!isset($this->templateData[$template]))
      $this->templateData[$template] = new ViewData();
    return $this->templateData[$template];
  }

  public function offsetSet($template, $value) {
  }

  public function offsetUnset($template) {
    unset($this->templateData[$template]);
  }
}