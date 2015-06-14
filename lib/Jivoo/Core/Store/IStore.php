<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * Reads and writes semi-structured data ("documents") with support for mutual
 * exclusion.
 */
interface IStore {
  /**
   * Open store for reading and optionally writing.
   * @param bool $mutable Whether writing should be enabled (with exclusive
   * locking).
   * @throws StoreReadFailedException If the store could not be opened.
   * @throws StoreLockException If the store is locked.
   */
  public function open($mutable = false);
  
  /**
   * Close store.
   */
  public function close();
  
  /**
   * Read data ("document") from store.
   * @return array Data.
   * @throws StoreReadFailedException If the data could not be read.
   */
  public function read();
  
  /**
   * Write data to store.
   * @param array $data Data.
   * @throws StoreWriteFailedException If the data could not be saved.
   */
  public function write(array $data);
  
  public function isMutable();
}