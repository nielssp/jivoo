<?php
/**
 * Collection of view data for templates.
 * @package Jivoo\View
 */
class ViewData implements ArrayAccess {
  /**
   * @var array Associative array of data.
   */
  private $data = array();
  
  /**
   * @var ViewData[] View data for specific templates.
   */
  private $templateData = array();
  
  /**
   * Get value of view variable.
   * @param string $property Variable name.
   * @return mixed Value ofvVariable.
   */
  public function __get($property) {
    return $this->data[$property];
  }
  
  /**
   * Set value of view variable.
   * @param string $property Variable name.
   * @param mixed $value Value of variable.
   */
  public function __set($property, $value) {
    $this->data[$property] = $value;
  }
  
  /**
   * Unset a view variable.
   * @param string $property Variable name.
   */
  public function __unset($property) {
    unset($this->data[$property]);
  }
  
  /**
   * Whether or not a view variable exists.
   * @param string $property Variable name.
   */
  public function __isset($property) {
    return isset($this->data[$property]);
  }
  
  /**
   * Get view data as an associative array.
   * @return array Associative array.
   */
  public function toArray() {
    return $this->data;
  }
  
  /**
   * @return true
   */
  public function offsetExists($template) {
    return true;
  }

  /**
   * Get view data for a specific template
   * @param string $template Template name.
   * @return ViewData The view data object for that template.
   */
  public function offsetGet($template) {
    if (!isset($this->templateData[$template]))
      $this->templateData[$template] = new ViewData();
    return $this->templateData[$template];
  }

  /**
   * Does not do anything.
   */
  public function offsetSet($template, $value) {
  }

  /**
   * Reset view data for a specific template.
   * @param string $template Template name.
   */
  public function offsetUnset($template) {
    unset($this->templateData[$template]);
  }
}