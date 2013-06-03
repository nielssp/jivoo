<?php
class Flash {
  private $session;
  
  private $message;
  private $type;
  private $label;
  private $uid;
  
  public function __construct(Session $session, $message, $type, $label = null) {
    $this->session = $session;
    $this->message = $message;
    $this->type = $type;
    $this->label = $label;
    $this->uid = md5($message);
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