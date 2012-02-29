<?php
/**
 * Blabla
 *
 * @package PeanutCMS
 */

/**
 * Abstract class that implements magic getters and setters.
 */
abstract class BaseObject {
  protected $_getters = array();
  protected $_setters = array();

  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property)) {
      return call_user_func(array($this, '_get_' . $property));
    }
    else if (in_array($property, $this->_setters)
             OR method_exists($this, '_set_' . $property)) {
      throw new PropertyWriteOnlyException(
        tr('Property "%1" is write-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
  }

  public function __set($property, $value) {
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property)) {
      call_user_func(array($this, '_set_' . $property), $value);
    }
    else if (in_array($property, $this->_getters)
             OR method_exists($this, '_get_' . $property)) {
      throw new PropertyReadOnlyException(
        tr('Property "%1" is read-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
  }
}

/* BaseObject exceptions */

class PropertyException extends Exception { }
class PropertyReadOnlyException extends PropertyException { }
class PropertyWriteOnlyException extends PropertyException { }
class PropertyNotFoundException extends PropertyException { }

