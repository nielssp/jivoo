<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemPoolInterface as CacheItemPool;

/**
 * An object cache for storing serializable values under keys. 
 */
interface Pool extends CacheItemPool {
  /**
   * Return value stored under a key.
   * @param string $key Item key.
   * @return mixed|null The stored value or null if not found.
   */
  public function get($key);
  
  /**
   * Store a value under a key.
   * @param string $key Item key.
   * @param mixed $value Value.
   * @param int|\DateInterval|\DateTime|null $expiration Timestamp or interval.
   * Null and the integer 0 is interpreted as 'no expiration date'. 
   * If the integer is less than or equal to 2,592,000 (30 days), the time
   * is relative to the current timestamp, otherwise it is interpreted as an
   * absolute UNIX timestamp. 
   * @return bool True if stored, false on failure.
   */
  public function set($key, $value, $expiration = 0);

  /**
   * Store a value under a key. Fails if the key already has a value.
   * @param string $key Item key.
   * @param mixed $value Value.
   * @param int|\DateInterval|\DateTime|null $expiration Expiration timestamp
   * {@see set}.
   * @return bool True if stored, false on failure.
   */
  public function add($key, $value, $expiration = 0);

  /**
   * Store a value under a key. Fails if the key doesn't exist.
   * @param string $key Item key.
   * @param mixed $value Value.
   * @param int|\DateInterval|\DateTime|null $expiration Expiration timestamp
   * {@see set}.
   * @return bool True if stored, false on failure.
   */
  public function replace($key, $value, $expiration = 0);

  /**
   * Updates the expiration time of a key. Fails if the key doesn't exist.
   * @param string $key Item key.
   * @param int|\DateInterval|\DateTime|null $expiration Expiration timestamp
   * {@see set}.
   * @return bool True if updated, false on failure.
   */
  public function touch($key, $expiration = 0);
  
  /**
   * Increment a numeric value.
   * @param string $key Item key.
   * @param int $offset Amount to increment value by.
   * @param int $init Initial value.
   * @return int New value.
   */
  public function increment($key, $offset = 1, $init = 0);
  
  /**
   * Decrement a numeric value.
   * @param string $key Item key.
   * @param int $offset Amount to decrement value by.
   * @param int $init Initial value.
   * @return int New value.
   */
  public function decrement($key, $offset = 1, $init = 0);
  
  /**
   * Delete/invalidate the value stored under a key.
   * @param string $key Item key.
   */
  public function delete($key);
}