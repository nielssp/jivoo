<?php
session_start();
require('../app/essentials.php');

abstract class Notification {

  public $uid;

  public $message;

  public function __construct($message, $uid = null, $readMore = null) {
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

  public function getType() {
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
}

abstract class LocalNotification extends Notification {}
abstract class GlobalNotification extends Notification {}

class LocalWarning extends LocalNotification {}
class GlobalWarning extends GlobalNotification {}

class LocalNotice extends LocalNotification {}
class GlobalNotice extends GlobalNotification {}

new LocalWarning("Wrong password");
new GlobalNotice("wtf");

foreach (GlobalNotification::all() as $notification) {
  echo '<p><strong>';
  echo $notification->getType();
  echo '</strong> ' . $notification->message;
  echo ' <a href="test-notifications.php?delete=';
  echo $notification->uid;
  echo '">[X]</a>';
  echo '</p>';
}
