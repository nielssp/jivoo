<?php
/**
 * An event.
 * @package Jivoo\Core
 */
class Event {
  /**
   * @var bool Has event been stopped.
   */
  public $stopped = false;
  
  /**
   * @var string|null Name of event.
   */
  public $name = null;
  
  /**
   * @var object|null Sender of event.
   */
  public $sender = null;
  
  /**
   * @var array Event parameters.
   */
  public $parameters = array();

  /**
   * Construct event.
   * @param object|null $sender Sender of event.
   * @param array $parameters Additional event paramters.
  */
  public function __construct($sender = null, $parameters = array()) {
    $this->sender = $sender;
    $this->parameters = $parameters;
  }

  /**
   * Stop propagation of event.
   */
  public function stopPropagation() {
    $this->stopped = true;
  }
}