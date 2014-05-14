<?php
class Event {

  private $stopped = false;
  private $sender = null;
  private $parameters = array();

  /**
   * Constructor.
  */
  public function __construct($sender = null, $parameters = array()) {
    $this->sender = $sender;
    $this->parameters = $parameters;
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

  public function stopPropagation() {
    $this->stopped = true;
  }
}