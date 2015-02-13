<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * A list of flash messages, i.e. {@see FlashMessage}.
 */
class FlashMessageList implements \ArrayAccess, \Countable, \Iterator {
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
   * Conmstruct message list.
   * @param string $type Message type.
   * @param string[] $list Messages.
   */
  public function __construct($type, $list = array()) {
    $this->type = $type;
    $this->list = $list;
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
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($key) {
    unset($this->list[$key]);
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
  }
  
  /**
   * Current message.
   * @return FlashMessage Message.
   */
  public function current() {
    unset($this->list[key($this->iterable)]);
    return new FlashMessage(current($this->iterable), $this->type);
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