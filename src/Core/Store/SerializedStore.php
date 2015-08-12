<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * Serializes data in files using {@see serialize} and {@see unserialize}.
 * 
 * Writing using {@see SerializedStore} should generally be slower than writing
 * using {@see JsonStore}, but reading using {@see SerializedStore} is generally
 * faster than {@see JsonStore}.
 */
class SerializedStore extends FileStore {
  /**
   * {@inheritdoc}
   */
  protected $defaultContent = "a:0:{}";
  
  /**
   * {@inheritdoc}
   */
  protected function encode(array $data) {
    return serialize($data);
  }
  
  /**
   * {@inheritdoc}
   */
  protected function decode($content) {
    return unserialize($content);
  }
}