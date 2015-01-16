<?php
/**
 * Iterator for use with {@see Map}, but can be used with any associative array.
 * @package Jivoo\Core
 */
class MapIterator implements Iterator {
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