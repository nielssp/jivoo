<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Helpers\Helper;

/**
 * Installation and setup helper class.
 * @package Jivoo\Setup
 * @property bool $done The current state of the current setup action, e.g.
 * true if the setup is done, false otherwise.
 */
class SetupHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Setup');

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    if ($property == 'done') {
      return $this->m->Setup->currentState;
    } 
    return parent::__get($property); 
  }

  /**
   * {@inheritdoc}
   */
  public function __set($property, $value) {
    if ($property == 'done') {
      $this->m->Setup->currentState = $value;
    }
    else
      return parent::__set($property, $value);
  }
  
  /**
   * Finish this setup action, then redirect to frontpage (i.e. continue to next
   * setup action, if there are more).
   */
  public function done() {
    $this->done = true;
    return $this->m->Routing->redirect(null);
  }
  
  /**
   * Set state of a setup action.
   * @param array|ILinkable|string|null $route Setup route, see {@see Routing}.
   * @param bool $done Whether or not the setup has finished.
   */
  public function setState($route, $done) {
    $this->m->Setup->setState($route, $done);
    return $this->m->Routing->redirect(null);
  }
  
  /**
   * Get state of a setup action.
   * @param array|ILinkable|string|null $route Setup route, see {@see Routing}.
   * @return bool True if setup has finished, false otherwise.
   */
  public function getState($route) {
    return $this->m->Setup->getState($route);
  }
}