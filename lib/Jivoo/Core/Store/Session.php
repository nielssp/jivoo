<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * A session is a document that ensures durability of changes. Unlike
 * {@see State}, changes are saved when they are made.
 */
class Session extends State {
  /**
   * Construct sessison.
   * @param IStore $store Store to load/save data from/to.
   * @param bool $mutable Whether session is mutable (true) or read-only (false).
   */
  public function __construct(IStore $store, $mutable = true) {
    parent::__construct();
    $this->store = $store;
    try {
      $this->store->open($mutable);
      $this->data = $this->store->read();
    }
    catch (StoreException $e) {
      throw new StateInvalidException(tr('Could not read state: %1', $e->getMessage()), null, $e);
    }
  }
  
  /**
   * {@inheritdoc}
   */
  protected function update() {
    if (!isset($this->store))
      throw new StateClosedException(tr('State already closed.'));
    if ($this->updated or !$this->store->isMutable())
      return;
    $this->store->write($this->data);
    $this->updated = false;
  }
  
  /**
   * Close, save (if mutable), and unlock state data.
   * @throws StateClosedException If the state has already been closed.
   */
  public function close() {
    if (!isset($this->store))
      throw new StateClosedException(tr('State already closed.'));
    if ($this->updated and $this->store->isMutable())
      $this->store->write($this->data);
    $this->store->close();
    $this->store = null;
  }
}