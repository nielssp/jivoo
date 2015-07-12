<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Collection of flash messages, i.e. error/warning/success messages for
 * the user of the web application.
 * 
 * Can be used from modules like this:
 * <code>
 * $this->session->flash->error[] = tr('An error occured.');
 * $this->session->flash->success = tr('Saved file.');
 * </code>
 * 
 */
class FlashMap implements \IteratorAggregate, \ArrayAccess, \Countable {
  /**
   * @var Session Session variables.
   */
  private $session;
  
  /**
   * @var FlashMessageList[] List of message lists
   */
  private $lists = array();
  
  /**
   * Construct collection.
   * @param Session $session Session storage.
   */
  public function __construct(Session $session) {
    $this->session = $session;
    if (!is_array($this->session['flash']))
      $this->session['flash'] = array();
    foreach ($this->session['flash'] as $type => $list) {
      $this->lists[$type] = new FlashMessageList($type, $list);
    }
  }
  
  /**
   * Save messages to session storage.
   */
  public function save() {
    $map = array();
    foreach ($this->lists as $type => $list) {
      if (count($list) > 0) {
        $map[$type] = $list->toArray();
      }
    }
    $this->session['flash'] = $map;
  }
  
  /**
   * Get message list associated with type.
   * @param string $type Message type, e.g. 'error', 'success'. 
   * @return FlashMessageList Message list.
   */
  public function __get($type) {
    if (!isset($this->lists[$type]))
      $this->lists[$type] = new FlashMessageList($type);
    return $this->lists[$type];
  }
  
  /**
   * Add a message to a list.
   * @param string $type Message type.
   * @param string $message Message.
   */
  public function __set($type, $message) {
    $this[$type][] = $message;
  }
  
  /**
   * Whether or not a message list contains any messages.
   * @param string $type Message type.
   * @return boolean True if not empty.
   */
  public function __isset($type) {
    return count($this[$type]) > 0;
  }
  
  /**
   * Clear a message list.
   * @param string $type Message type.
   */
  public function __unset($type) {
    $this[$type]->clear();
  }
  
  /**
   * Get message list associated with type.
   * @param string $type Message type, e.g. 'error', 'success'. 
   * @return FlashMessageList Message list.
   */
  public function offsetGet($type) {
    return $this->__get($type);
  }

  /**
   * Whether or not a message list contains any messages.
   * @param string $type Message type.
   * @return boolean True if not empty.
   */
  public function offsetExists($type) {
    return $this->__isset($type);
  }
  
  /**
   * Add a message to a list.
   * @param string $type Message type.
   * @param string $message Message.
   */
  public function offsetSet($type, $message) {
    if (isset($type))
      $this->__set($type, $message);
  }

  /**
   * Clear a message list.
   * @param string $type Message type.
   */
  public function offsetUnset($type) {
    $this->__unset($type);
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
    return new FlashMapIterator($this, $this->lists);
  }
}