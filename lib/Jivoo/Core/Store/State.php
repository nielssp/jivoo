<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

class State extends Document {
  private $store = null;
  
  protected $saveDefaults = false;
  
  public function __construct(IStore $store) {
    parent::__construct();
    $this->store = $store;
    $this->store->open(true);
    $this->data = $this->store->read();
  }
  
  public function close() {
    if (!isset($this->store))
      throw new StateClosedException(tr('State already closed.'));
    if ($this->updated)
      $this->store->write($this->data);
    $this->store->close();
    $this->store = null;
  }
}