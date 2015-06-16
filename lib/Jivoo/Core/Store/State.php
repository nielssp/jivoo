<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * A state is a document that (unlike {@see Config}) ensures durability of
 * changes. Thus, if the state is mutable, the assiciated {@see IStore} will
 * be exclusively locked until {@see close()} is called.  
 */
class State extends Document {
  /**
   * @var IStore
   */
  private $store = null;

  /**
   * {@inheritdoc}
   */
  protected $saveDefaults = false;

  /**
   * Construct state.
   * @param IStore $store Store to load/save data from/to.
   */
  public function __construct(IStore $store, $mutable = true) {
    parent::__construct();
    $this->store = $store;
    $this->store->open($mutable);
    $this->data = $this->store->read();
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