<?php
abstract class Notification {

  private $uid;

  private $message;

  public function __get($property) {
    switch ($property) {
      case 'uid':
        return $this->uid;
      case 'message':
        return $this->message;
      case 'type':
        return $this->getType();
      case 'label':
        return $this->getLabel();
      default:
        throw new Exception(tr('Invalid property'));
    }
  }

  public function __construct($message, $uid = NULL, $readMore = NULL) {
    $type = get_class($this);
    $this->message = $message;
    if (!isset($uid)) {
      $uid = md5($message);
    }
    $this->uid = $uid;
    if (!isset($_SESSION[SESSION_PREFIX . 'notifications'])
        OR !is_array($_SESSION[SESSION_PREFIX . 'notifications'])) {
      $_SESSION[SESSION_PREFIX . 'notifications'] = array();
    }
    $_SESSION[SESSION_PREFIX . 'notifications'][$uid] = $this;
  }

  public function delete() {
    unset($_SESSION[SESSION_PREFIX . 'notifications'][$this->uid]);
  }

  public static function all() {
    $type = get_called_class();
    $result = array();
    foreach ($_SESSION[SESSION_PREFIX . 'notifications'] as $uid => $obj) {
      if (is_a($obj, $type) OR is_subclass_of($obj, $type)) {
        $result[] = $obj;
      }
    }
    return $result;
  }

  public static function count() {
    $type = get_called_class();
    $result = 0;
    foreach ($_SESSION[SESSION_PREFIX . 'notifications'] as $uid => $obj) {
      if (is_a($obj, $type) OR is_subclass_of($obj, $type)) {
        $result++;
      }
    }
    return $result;
  }

  private function getLabel() {
    $type = get_class($this);
    switch ($type) {
      case 'LocalError':
      case 'GlobalError':
        return tr('Error');
      case 'LocalWarning':
      case 'GlobalWarning':
        return tr('Warning');
      case 'LocalNotice':
      case 'GlobalNotice':
      default:
        return tr('Notice');
    }
  }

  private function getType() {
    $type = get_class($this);
    switch ($type) {
      case 'LocalError':
      case 'GlobalError':
        return 'error';
      case 'LocalWarning':
      case 'GlobalWarning':
        return 'warning';
      case 'LocalNotice':
      case 'GlobalNotice':
      default:
        return 'notice';
    }
  }
}

