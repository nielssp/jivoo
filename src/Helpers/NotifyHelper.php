<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\Utilities;
use Jivoo\InvalidPropertyException;
use Jivoo\Core\Assume;
use Jivoo\Core\Store\Document;

/**
 * Display notifications.
 * @method void error($message) Add error.
 * @method void error($message) Add error.
 */
class NotifyHelper extends Helper implements \Countable, \IteratorAggregate {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('session');
  
  /**
   * @var NotificationList[]
   */
  private $lists = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    if (!is_array($this->m->session->get('notify')))
      $this->m->session['notify'] = array();
    foreach ($this->m->session['notify'] as $type => $list) {
      $this->lists[$type] = new NotificationList($this->m->session['notify'], $type);
    }
  }

  /**
   * Get message list associated with type.
   * @param string $type Message type, e.g. 'error', 'success'.
   * @return NotificationList Message list.
   */
  public function __get($type) {
    if (!isset($this->lists[$type]))
      $this->lists[$type] = new NotificationList($this->m->session['notify'], $type);
    return $this->lists[$type];
  }
  
  /**
   * Add a message to a list.
   * @param string $type Message type.
   * @param string $message Message.
   */
  public function __set($type, $message) {
    $this->__get($type)->offsetSet(null, $message);
  }
  
  /**
   * Whether or not a message list contains any messages.
   * @param string $type Message type.
   * @return boolean True if not empty.
   */
  public function __isset($type) {
    return count($this->__get($type)) > 0;
  }
  
  /**
   * Clear a message list.
   * @param string $type Message type.
   */
  public function __unset($type) {
    $this->__get($type)->clear();
  }
  
  
  /**
   * Create notification.
   * @param string $type Notification type, e.g. 'error' or 'success'.
   * @param string[] $parameters An array containing the notification message.
   */
  public function __call($type, $parameters) {
    assume(isset($parameters[0]));
    Assume::isString($parameters[0]);
    $this->__set($type, $parameters[0]); 
  }

  /**
   * Count number messages in all lists.
   * @return int Total number of messages.
   */
  public function count() {
    $count = 0;
    foreach ($this->lists as $list) {
      $count += $list->count();
    }
    return $count;
  }
  
  /**
   * Get iterator for all lists.
   * @return FlashMapIterator Iterator.
   */
  public function getIterator() {
    return new NotificationIterator($this, $this->lists);
  }
}

/**
 * An iterator for {@see NotifyHelper}.
 */
class NotificationIterator implements \Iterator {
  /**
   * @var NotifyHelper
   */
  private $Notify;

  /**
   * @var array Messages.
   */
  private $list = array();

  /**
   * Construct iterator
   * @param NotifyHelper $Notify Helper.
   * @param string[] $lists Names of lists to iterate.
  */
  public function __construct(NotifyHelper $Notify, $lists) {
    $this->Notify = $Notify;
    foreach ($lists as $type => $list) {
      $array = $list->toArray();
      foreach ($array as $index => $message) {
        $this->list[] = array($index, new Notification($message, $type));
      }
    }
  }

  /**
   * Current message.
   * @return FlashMessage Message.
   */
  public function current() {
    $tuple = current($this->list);
    $index = $tuple[0];
    $type = $tuple[1]->type;
    $this->Notify->__get($type)->offsetUnset($index);
    return $tuple[1];
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    next($this->list);
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return key($this->list);
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return key($this->list) !== null;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    reset($this->list);
  }
}

/**
 * A list of notifications, i.e. {@see Notification}.
 */
class NotificationList implements \ArrayAccess, \Countable, \Iterator {
  /**
   * @var string Message type.
   */
  private $type;
  
  /**
   * @var string[] Messages.
   */
  private $list;
  
  /**
   * @var Traversable
   */
  private $iterable;
  
  /**
   * @var Document
   */
  private $session;
  
  /**
   * Conmstruct message list.
   * @param Document $session Notifications.
   * @param string $type Message type.
   */
  public function __construct(Document $session, $type) {
    $this->type = $type;
    $this->list = $session->get($type, array());
    $this->session = $session;
  }
  
  /**
   * Convert to array.
   * @return string[] Array of messages.
   */
  public function toArray() {
    return $this->list;
  }
  
  /**
   * {@inheritdoc}
   */
  public function offsetGet($key) {
    return $this->list[$key];
  }
  
  /**
   * {@inheritdoc}
   */
  public function offsetExists($key) {
    return isset($this->list[$key]);
  }
  
  /**
   * {@inheritdoc}
   */
  public function offsetSet($key, $value) {
    if (!isset($key)) {
      $this->list[] = $value;
    }
    else {
      $this->list[$key] = $value;
    }
    $this->session[$this->type] = $this->list;
  }
  
  /**
   * {@inheritdoc}
   */
  public function offsetUnset($key) {
    unset($this->list[$key]);
    $this->session[$this->type] = $this->list;
  }
  
  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->list);
  }
  
  /**
   * Empty the list
   */
  public function clear() {
    $this->list = array();
    $this->session[$this->type] = $this->list;
  }
  
  /**
   * Current message.
   * @return FlashMessage Message.
   */
  public function current() {
    unset($this->list[key($this->iterable)]);
    $this->session[$this->type] = $this->list;
    return new Notification(current($this->iterable), $this->type);
  }
  
  /**
   * {@inheritdoc}
   */
  public function next() {
    next($this->iterable);
  }
  
  /**
   * {@inheritdoc}
   */
  public function key() {
    return key($this->iterable);
  }
  
  /**
   * {@inheritdoc}
   */
  public function valid() {
    return key($this->iterable) !== null;
  }
  
  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->iterable = $this->list;
    reset($this->iterable);
  }
}

/**
 * A message to display to the user.
 */
class Notification {
  /**
   * @var string Message.
   */
  public $message;
  
  /**
   * @var string Message type, e.g. 'error' or 'success'.
   */
  public $type;
  
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
   * Convert to string (returns the message).
   * @return string The message.
   */
  public function __toString() {
    return $this->message;
  }
}