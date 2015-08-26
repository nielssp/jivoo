<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\Core\Store\Document;

/**
 * An initialization unit.
 */
abstract class UnitBase extends Module implements Unit {
  /**
   * @var string[] Names of unit dependencies. 
   */
  protected $requires = array();

  /**
   * @var string[] Names of units that should run before this one if they are
   * enabled.
   */
  protected $after = array();

  /**
   * @var string[] Names of units that should run after this one if they are
   * enabled.
   */
  protected $before = array();

  /**
   * {@inheritdoc}
   */
  public function stop(App $app, Document $config) {}
  
  /**
   * {@inheritdoc}
   */
  public function requires() {
    return $this->requires;
  }

  /**
   * {@inheritdoc}
   */
  public function after() {
    return $this->after;
  }

  /**
   * {@inheritdoc}
   */
  public function before() {
    return $this->before;
  }
}