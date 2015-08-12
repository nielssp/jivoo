<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

use Jivoo\Core\Json;
use Jivoo\Core\JsonException;

/**
 * Stores data as JSON files.
 * 
 * Writing using {@see JsonStore} should generally be faster than writing using
 * {@see PhpStore} or {@see SerializedStore}, but reading using {@see JsonStore}
 * is generally slower than {@see SerializedStore}.
 * 
 * JSON (especially with {@see $prettyPrint} set to true) is ideal for
 * configuration files that can be read and modified by a user.
 */
class JsonStore extends FileStore {
  /**
   * {@inheritdoc}
   */
  protected $defaultContent = "{}";
  
  /**
   * @var bool Whether to pretty print the file. 
   */
  public $prettyPrint = true;
  
  /**
   * {@inheritdoc}
   */
  protected function encode(array $data) {
    if ($this->prettyPrint)
      return Json::prettyPrint($data);
    return Json::encode($data);
  }
  
  /**
   * {@inheritdoc}
   */
  protected function decode($content) {
    try {
      return Json::decode($content);
    }
    catch (JsonException $e) {
      throw new AccessException(
        'Invalid JSON file: ' . $e->getMessage(), 0, $e
      );
    }
  }
}