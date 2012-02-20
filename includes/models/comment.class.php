<?php
/**
 * @brief Blog comment data model
 */

class Comment {
  private $id;
  private $content;
  private $date;
  private $state;
  
  private $updated;
  
  /* Properties begin */
  private $_getters = array('name', 'title', 'content', 'date', 'state', 'comments', 'commenting');
  private $_setters = array('name', 'title', 'content', 'date', 'state', 'commenting');
  
  /**
   * Magic method
   * @param string $property
   * @throws Exception
   */
  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property))
      return call_user_func(array($this, '_get_' . $property));
    else if (in_array($property, $this->_setters) OR method_exists($this, '_set_' . $property))
      throw new Exception('Property "' . $property . '" is write-only.');
    else
      throw new Exception('Property "' . $property . '" is not accessible.');
  }
  
  public function __set($property, $value) {
    $this->updated = true;
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property))
      call_user_func(array($this, '_set_' . $property), $value);
    else if (in_array($property, $this->_getters) OR method_exists($this, '_get_' . $property))
      throw new Exception('Property "' . $property . '" is read-only.');
    else
      throw new Exception('Property "' . $property . '" is not accessible.');
  }
  /* Properties end */

  public function __construct() {
    $this->title = '';
    $this->updated = false;
  }

  public function __destruct() {
    return true;
  }
  
  public function commit() {
    if (!$this->updated)
      return;
    echo 'Updating database';
  }

}
