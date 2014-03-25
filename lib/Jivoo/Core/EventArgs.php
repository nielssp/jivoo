<?php
/**
 * An empty EventArgs class for use with Events.
 * When extended, protected member variables are made
 * available as read-only properties. These properties
 * can be set using the constructor.
 * @package Core
 */
class EventArgs {
  /**
   * Constructor.
   * Additional parameters can be used to set values
   * of properties.
   * @param mixed $var,... Value of properties
   */
  public function __construct() {
    $properties = get_object_vars($this);
    $args = func_get_args();
    $argc = func_num_args();
    $argi = 0;
    foreach ($properties as $property => $value) {
      if ($argi >= $argc) {
        break;
      }
      if (isset($args[$argi])) {
        $this->$property = $args[$argi];
      }
      $argi++;
    }
  }

  /**
   * Get the value of a property.
   * @param string $property Name of property
   * @return mixed Value of property
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
  }
}
