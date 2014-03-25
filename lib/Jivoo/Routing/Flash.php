<?php
/**
 * A message to flash to the user
 * @package Jivoo\Routing
 * @property-read string $message Message
 * @property-read string $type Message type, 'alert' or 'notice'
 * @property-read string $uid Message uid
 * @property-read string $label Label
 */
class Flash {
  /**
   * @var Session Current session
   */
  private $session;
  
  /**
   * @var string Message
   */
  private $message;
  
  /**
   * @var string Type of message
   */
  private $type;
  
  /**
   * @var string Label
   */
  private $label;
  
  /**
   * @var string Message uid
   */
  private $uid;
  
  /**
   * Constructor.
   * @param Session $session Current session
   * @param string $message Message
   * @param string $type Message type, 'alert' or 'notice'
   * @param string $label Message label, default is tr('Alert') for alerts and
   * tr('Notice') for notices.
   * @param string $uid Message uid, default is md5 sum of $message
   */
  public function __construct(Session $session, $message, $type, $label = null, $uid = null) {
    $this->session = $session;
    $this->message = $message;
    $this->type = $type;
    $this->label = $label;
    if (!isset($uid)) {
      $uid = md5($message);
    }
    $this->uid = $uid;
  }
  
  /**
   * Creata flash message from a 4-tuple
   * @param Session $session Current session
   * @param string[] $array 4-tuple of message, type, label and uid
   * @return Flash Flash message object
   */
  public static function fromArray(Session $session, $array) {
    return new Flash($session, $array[0], $array[1], $array[2], $array[3]);
  }
  
  /**
   * Create a 4-tuple
   * @return string[] 4-tuple of message, type, label and uid
   */
  public function toArray() {
    return array($this->message, $this->type, $this->label, $this->uid);
  }
  
  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'uid':
      case 'type':
      case 'message':
        return $this->$property;
      case 'label':
        return $this->getLabel();
    }
  }
  
  /**
   * Get label for message
   * @return string Label
   */
  private function getLabel() {
    if (isset($this->label)) {
      return $this->label;
    }
    switch ($this->type) {
      case 'alert':
        return tr('Alert');
      default:
        return tr('Notice');
    }
  }
  
  /**
   * Delete flash message
   */
  public function delete() {
    $messages = $this->session['messages'];
    unset($messages[$this->uid]);
    $this->session['messages'] = $messages;
  }
}