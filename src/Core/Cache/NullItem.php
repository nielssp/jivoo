<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemInterface as CacheItem;

/**
 * A cache item without a value.
 */
class NullItem implements CacheItem {
  /**
   * @var string
   */
  private $key;

  /**
   * Construct item.
   * @param string $key Item key.
   */
  public function __construct($key) {
    $this->key = $key;
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
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function set($value) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isHit() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function exists() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAt($expiration) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAfter($time) {
    return $this;
  }
}