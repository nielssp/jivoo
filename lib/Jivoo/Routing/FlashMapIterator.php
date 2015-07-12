<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * An iterator for {@see FlashMap}.
 */
class FlashMapIterator implements \Iterator {
  /**
   * @var FlashMap Flash map.
   */
  private $flash;
  
  /**
   * @var array Messages.
   */
  private $list = array();
  
  /**
   * Construct iterator
   * @param FlashMap $flash Flash map.
   * @param string[] $lists Names of lists to iterate.
   */
  public function __construct(FlashMap $flash, $lists) {
    $this->flash = $flash;
    foreach ($lists as $type => $list) {
      $array = $list->toArray();
      foreach ($array as $index => $message) {
        $this->list[] = array($index, new FlashMessage($message, $type));
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
    unset($this->flash[$type][$index]);
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
