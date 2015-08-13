<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * A state is a document that (unlike {@see Config}) ensures durability of
 * changes. Thus, if the state is mutable, the assiciated {@see Store} will
 * be exclusively locked until {@see close()} is called.  
 */
class State extends Document {
  /**
   * @var Store
   */
  private $store = null;

  /**
   * {@inheritdoc}
   */
  protected $saveDefaults = false;

  /**
   * Construct state.
   * @param Store $store Store to load/save data from/to.
   * @param bool $mutable Whether state is mutable (true) or read-only (false).
   * @throws AccessException If state could not be read.
   */
  public function __construct(Store $store, $mutable = true) {
    parent::__construct();
    $this->store = $store;
    try {
      $this->store->open($mutable);
      $this->data = $this->store->read();
    }
    catch (AccessException $e) {
      throw new AccessException(tr('Could not read state: %1', $e->getMessage()), null, $e);
    }
  }
  
  /**
   * Whether state is open.
   * @return bool True if open.
   */
  public function isOpen() {
    return isset($this->store);
  }
  
  /**
   * Whether state is open and mutable.
   * @return bool True if mutable.
   */
  public function isMutable() {
    return isset($this->store) and $this->store->isMutable();
  }
  
  /**
   * Close, save (if mutable), and unlock state data.
   * @throws NotOpenException If the state has already been closed.
   */
  public function close() {
    if (!isset($this->store))
      throw new NotOpenException(tr('State already closed.'));
    if ($this->updated and $this->store->isMutable())
      $this->store->write($this->data);
    $this->store->close();
    $this->store = null;
    $this->updated = false;
  }
}