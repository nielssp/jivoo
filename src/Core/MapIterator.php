<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Iterator for use with associative arrays.
 */
class MapIterator implements \Iterator {
  /**
   * @var array Map to iterate.
   */
  private $map;
  
  /**
   * Construct iterator from associative array.
   * @param array $map Associative array.
   */
  public function __construct($map) {
    $this->map = $map;
  }
  
  /**
   * {@inheritdoc}
   */
  public function current() {
    return current($this->map);
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    next($this->map);
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return key($this->map);
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return key($this->map) !== null;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    reset($this->map);
  }
}