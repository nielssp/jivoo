<?php
class Event {

  public $stopped = false;
  public $name = null;
  public $sender = null;
  public $parameters = array();

  /**
   * Constructor.
  */
  public function __construct($sender = null, $parameters = array()) {
    $this->sender = $sender;
    $this->parameters = $parameters;
  }

  public function stopPropagation() {
    $this->stopped = true;
  }
}