<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Event sent before and after an object has been loaded (e.g. LoadableModule,
 * Helper, Extension etc.)
 */
class LoadEvent extends Event {
  /**
   * @var string Class name of loaded object.
   */
  public $class;
  
  /**
   * @var bool Whether or not the object has been loaded.
   */
  public $loaded = false;
  
  /**
   * @var Module|null Object if loaded.
   */
  public $object;
  
  /**
   * Construct load event.
   * @param object $sender Sender of event.
   * @param string $class Name of class.
   * @param Module $object Object if loaded.
   */
  public function __construct($sender, $class, Module $object = null) {
    parent::__construct($sender);
    $this->class = $class;
    if (isset($object)) {
      $this->loaded = true;
      $this->object = $object;
    }
  }
}