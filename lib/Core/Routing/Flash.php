<?php
class Flash {
  private $session;
  
  private $message;
  private $type;
  private $label;
  private $uid;
  
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
  
  public static function fromArray(Session $session, $array) {
    return Flash($session, $array[0], $array[1], $array[2], $array[3]);
  }
  
  public function toArray() {
    return array($this->message, $this->type, $this->label, $this->uid);
  }
  
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
  
  public function delete() {
    $messages = $this->session['messages'];
    unset($messages[$this->uid]);
    $this->session['messages'] = $messages;
  }
}