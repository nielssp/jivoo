<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

class Config extends Document {
  private $store = null;
  
  protected $saveDefaults = true;
  
  public function __construct(IStore $store = null) {
    parent::__construct();
    if (isset($store)) {
      $this->store = $store;
      $this->store->open(false);
      $this->data = $this->store->read();
      $this->store->close();
    }
  }
  
  public function save() {
    if ($this->root !== $this)
      return $this->root->save();
    if (!isset($this->store))
      return false;
    if (!$this->updated)
      return true;
    $this->store->open(true);
    $this->store->write($this->data);
    $this->store->close();
    return true;
  }
  
  protected function createEmpty() {
    return new Config();
  }
}