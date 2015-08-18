<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemInterface as CacheItem;

abstract class PoolBase implements Pool {
  /**
   * {@inheritdoc}
   */
  public function getItems(array $keys = array()) {
    $items = array();
    foreach ($keys as $key)
      $items[] = $this->getItem($key);
    return $items;
  }
  
  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItem $item) {
    $this->save();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    return $this->getItem($key)->get();
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value, $expiration = 0) {
    $item = $this->getItem($key);
    $item->set($value);
    $item->expiresAt(self::convertExpiration($expiration));
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function add($key, $value, $expiration = 0) {
    $item = $this->getItem($key);
    if ($item->isHit())
      return false;
    $item->set($value);
    $item->expiresAt(self::convertExpiration($expiration));
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function replace($key, $value, $expiration = 0) {
    $item = $this->getItem($key);
    if (!$item->isHit())
      return false;
    $item->set($value);
    $item->expiresAt(self::convertExpiration($expiration));
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function touch($key, $expiration = 0) {
    $item = $this->getItem($key);
    if (!$item->isHit())
      return false;
    $item->expiresAt(self::convertExpiration($expiration));
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key, $offset = 1, $init = 0) {
    $item = $this->getItem($key);
    if ($item->isHit())
      $value = $item->get() + $offset;
    else
      $value = $init;
    $item->set($value);
    $this->save($item);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function decrement($key, $offset = 1, $init = 0) {
    $item = $this->getItem($key);
    if ($item->isHit())
      $value = $item->get() - $offset;
    else
      $value = $init;
    $item->set($value);
    $this->save($item);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    $this->deleteItems(array($key));
  }
  
  /**
   * Converts expiration timestamp, interval, {\DateInterval} or {\DateTime}
   * to a {\DateTime} or null (for no expiration date).
   * @param int|\DateInterval|\DateTime|null $expiration Timestamp or interval.
   * Null and the integer 0 is interpreted as 'no expiration date'. 
   * If the integer is less than or equal to 2,592,000 (30 days), the time
   * is relative to the current timestamp, otherwise it is interpreted as an
   * absolute UNIX timestamp. 
   * @return \DateTime|null
   */
  public static function convertExpiration($expiration) {
    if ($expiration === null or $expiration === 0)
      return null;
    if (is_int($expiration)) {
      if ($expiration <= 2592000)
        $expiration += time();
      return \DateTime::createFromFormat('U', $expiration);
    }
    if ($expiration instanceof \DateTime)
      return $expiration;
    assume($expiration instanceof \DateInterval);
    $d = new \DateTime();
    return $d->add($expiration);
  }
}