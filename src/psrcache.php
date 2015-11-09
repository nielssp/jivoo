<?php
// PSR-6 (Proposed) caching interfaces. May change in the future.
// See https://github.com/php-fig/fig-standards/blob/master/proposed/cache.md
// and https://github.com/php-fig/fig-standards/blob/master/proposed/cache-meta.md
namespace Psr\Cache;

interface CacheException {}

interface InvalidArgumentException extends CacheException {}

interface CacheItemInterface {
  /**
   * @return string
   */
  public function getKey();

  /**
   * @return mixed
   */
  public function get();

  /**
   * @param mixed $value
   * @return static
   */
  public function set($value);

  /**
   * @return bool
   */
  public function isHit();

  /**
   * @param \DateTimeInterface $expiration
   * @return static
   */
  public function expiresAt($expiration);

  /**
   * @param int|\DateInterval $time
   * @return static
   */
  public function expiresAfter($time);
}

interface CacheItemPoolInterface {
  /**
   * @param string $key
   * @return \Psr\Cache\CacheItemInterface
   * @throws \Psr\Cache\InvalidArgumentException
   */
  public function getItem($key);

  /**
   * @param array $keys
   * @return array|\Traversable
   */
  public function getItems(array $keys = array());
  
  /**
   * @param string $key
   * @throws \Psr\Cache\InvalidArgumentException
   * @return bool
   */
  public function hasItem($key);

  /**
   * @return bool
   */
  public function clear();

  /**
   * @param string $key
   * @throws \Psr\Cache\InvalidArgumentException
   * @return bool
   */
  public function deleteItem($key);

  /**
   * @param array $keys
   * @throws \Psr\Cache\InvalidArgumentException
   * @return static
   */
  public function deleteItems(array $keys);

  /**
   * @param CacheItemInterface $item
   * @return bool
   */
  public function save(CacheItemInterface $item);

  /**
   * @param CacheItemInterface $item
   * @return bool
   */
  public function saveDeferred(CacheItemInterface $item);

  /**
   * @return bool
   */
  public function commit();

}