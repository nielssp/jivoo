<?php
/**
 * A message to flash to the user.
 * @package Jivoo\Routing
 * @property-read string $message Message.
 * @property-read string $type Message type, e.g. 'error' or 'success'.
 */
class FlashMessage {
  /**
   * @var string Message.
   */
  private $message;
  
  /**
   * @var string Type of message.
   */
  private $type;
  
  /**
   * Construct message.
   * @param string $message Message.
   * @param string $type Message type, e.g. 'error' or 'success'.
   */
  public function __construct($message, $type) {
    $this->message = $message;
    $this->type = $type;
  }
  
  /**
   * Get value of property.
   * @param string $property Property name.
   * @return mixed Value.
   */
  public function __get($property) {
    switch ($property) {
      case 'type':
      case 'message':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Convert to string (returns the message).
   * @return string The message.
   */
  public function __toString() {
    return $this->message;
  }
}