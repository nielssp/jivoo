<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemPoolInterface;

/**
 * An object cache for storing serializable values under keys. 
 */
interface Pool extends CacheItemPoolInterface {
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
   * @param int $expiration Expiration timestamp in seconds. Default is 0
   * (never expires). If less than or equal to 2,592,000 (30 days), the time
   * is relative to the current timestamp, otherwise it is interpreted as an
   * absolute UNIX timestamp. 
   * @return bool True if stored, false on failure.
   */
  public function set($key, $value, $expiration = 0);

  /**
   * Store a value under a key. Fails if the key already has a value.
   * @param string $key Item key.
   * @param mixed $value Value.
   * @param int $expiration Expiration timestamp in seconds. {@see set}.
   * @return bool True if stored, false on failure.
   */
  public function add($key, $value, $expiration = 0);

  /**
   * Store a value under a key. Fails if the key doesn't exist.
   * @param string $key Item key.
   * @param mixed $value Value.
   * @param int $expiration Expiration timestamp in seconds. {@see set}.
   * @return bool True if stored, false on failure.
   */
  public function replace($key, $value, $expiration = 0);

  /**
   * Updates the expiration time of a key. Fails if the key doesn't exist.
   * @param string $key Item key.
   * @param int $expiration Expiration timestamp in seconds. {@see set}.
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
   * Delete/invalidate the value stored under a key. Fails if the key doesn't
   * exist.
   * @param string $key Item key.
   * @return bool True if one ore more items were deleted, false on failure.
   */
  public function delete($key);
}