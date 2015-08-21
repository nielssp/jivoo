<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemInterface as CacheItem;

/**
 * A mutable cache item that with a value and an expiration date.
 */
class MutableItem implements CacheItem {
  /**
   * @var string
   */
  private $key;

  /**
   * @var string
   */
  private $value;
  
  /**
   * @var bool
   */
  private $hit;

  /**
   * @var \DateTimeInterface|null
   */
  private $expiration;

  /**
   * Construct item.
   * @param string $key Item key.
   * @param mixed $value Item value.
   * @param bool $hit Whether the value is valid.
   * @param \DateTimeInterface|null $expiration Expiration time if any.
   */
  public function __construct($key, $value, $hit, \DateTimeInterface $expiration = null) {
    $this->key = $key;
    $this->value = $value;
    $this->hit = $hit;
    $this->expiration = $expiration;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    if ($this->hit)
      return $this->value;
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function set($value) {
    $this->value = $value;
    $this->hit = true;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isHit() {
    return $this->hit;
  }

  /**
   * {@inheritdoc}
   */
  public function exists() {
    // ????
    return $this->hit;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAt($expiration) {
    $this->expiration = $expiration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAfter($time) {
    $this->expiration = new \DateTime();
    if (is_int($time)) {
      if ($time < 0) {
        $time = new \DateInterval('PT' . abs($time) . 'S');
        $time->invert = 1;
      }
      else {
        $time = new \DateInterval('PT' . $time . 'S');
      }
    }
    $this->expiration->add($time);
    return $this;
  }

  /**
   * Get the expiration of the item if any.
   * @return \DateTime|null Expiration date or null of item does not expire.
   */
  public function getExpiration() {
    return $this->expiration;
  }
}