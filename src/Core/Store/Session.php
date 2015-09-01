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
class Session extends Document {
  /**
   * @var Store
   */
  private $store;
  
  /**
   * {@inheritdoc}
   */
  protected $saveDefaults = false;
  
  /**
   * Construct state.
   * @param Store $store Store to load/save data from/to.
   * @throws AccessException If state could not be read.
   */
  public function __construct(Store $store) {
    parent::__construct();
    $this->store = $store;
    try {
      $this->store->open(true);
      $this->data = $this->store->read();
    }
    catch (AccessException $e) {
      throw new AccessException(tr('Could not open session: %1', $e->getMessage()), null, $e);
    }
  }
  
  /**
   * Whether state is open.
   * @return bool True if open.
   */
  public function isOpen() {
    return $this->store->isMutable();
  }

  /**
   * Reopen a closed session.
   * @return boolean True on success, false on failure.
   */
  public function open() {
    $this->store->open(true);
  }

  /**
   * Close, save (if mutable), and unlock state data.
   * @throws NotOpenException If the state has already been closed.
   */
  public function close() {
    if ($this->updated and $this->store->isMutable())
      $this->store->write($this->data);
    $this->store->close();
    $this->updated = false;
  }

  /**
   * {@inheritdoc}
   */
  protected function update() {
    if (!isset($this->store))
      throw new NotOpenException(tr('Session already closed.'));
    if (!$this->updated)
      return;
    $this->store->write($this->data);
    $this->updated = false;
  }
}