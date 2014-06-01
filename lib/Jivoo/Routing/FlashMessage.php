<?php
/**
 * A message to flash to the user
 * @package Jivoo\Routing
 * @property-read string $message Message
 * @property-read string $type Message type, e.g. 'alert' or 'notice'
 */
class FlashMessage {
  /**
   * @var string Message
   */
  private $message;
  
  /**
   * @var string Type of message
   */
  private $type;
  
  /**
   * Constructor.
   * @param string $message Message
   * @param string $type Message type, 'alert' or 'notice'
   */
  public function __construct($message, $type) {
    $this->message = $message;
    $this->type = $type;
  }
  
  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'type':
      case 'message':
        return $this->$property;
    }
  }

  public function __toString() {
    return $this->message;
  }
}