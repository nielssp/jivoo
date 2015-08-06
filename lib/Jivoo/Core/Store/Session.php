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
   * {@inheritdoc}
   */
  protected function update() {
    if (!isset($this->store))
      throw new NotOpenException(tr('State already closed.'));
    if ($this->updated or !$this->store->isMutable())
      return;
    $this->store->write($this->data);
    $this->updated = false;
  }
}