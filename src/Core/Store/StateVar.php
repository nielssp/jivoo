<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

class StateVar {
  /**
   * @var State
   */
  private $state;
  
  /**
   * @var string
   */
  private $var;
  
  public function __construct(State $state, $var) {
    $this->state = $state;
    $this->var = $var;
  }
  
  public function get() {
    return $this->state->get($this->var);
  }
  
  public function setDefault($value) {
    $this->state->setDefault($this->var, $value);
  }
  
  public function exists() {
    return $this->state->exists($this->var);
  }
  
  public function set($value) {
    $this->state->set($this->var, $value);
  }
}