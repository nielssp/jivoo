<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\InvalidMethodException;

/**
 * An object that can record method and setter calls and play them back later in
 * the same order.
 * 
 * Can be used to manipulate an object that doesn't exist yet. Methods do not
 * return anything, and any error will be delayed until the macro is played back. 
 */
class ObjectMacro implements \ArrayAccess {
  /**
   * @var array[] Sequence of actions.
   */
  private $actions = array();
  
  /**
   * Record a method call.
   * @param string $method Method name.
   * @param mixed[] $args Parameters.
   * @return self Self;
   */
  public function __call($method, $args) {
    $this->actions[] = array('call', $method, $args);
    return $this;
  }
  
  /**
   * Record the setting of a property.
   * @param string $property Property name.
   * @param mixed $value Property value.
   */
  public function __set($property, $value) {
    $this->actions[] = array('set', $property, $value);
  }
  
  /**
   * Record the unsetting of a property.
   * @param string $property Property name.
   */
  public function __unset($property) {
    $this->actions[] = array('unset', $property);
  }
  
  /**
   * Not supported.
   * @param mixed $offset Offset.
   * @throws InvalidMethodException When called.
   */
  public function offsetExists($offset) {
    throw new InvalidMethodException(tr('Macro is write-only.'));
  }

  /**
   * Not supported.
   * @param mixed $offset Offset.
   * @throws InvalidMethodException When called.
   */
  public function offsetGet($offset) {
    throw new InvalidMethodException(tr('Macro is write-only.'));
  }
  
  /**
   * Record the setting of an offset.
   * @param mixed $offset Offset.
   * @param mixed $value Value.
   */
  public function offsetSet($offset, $value) {
    $this->actions[] = array('offsetSet', $offset, $value);
  }

  /**
   * Record the unsetting of an offset.
   * @param mixed $offset Offset.
   */
  public function offsetUnset($offset) {
    $this->actions[] = array('offsetUnset', $offset);
  }
  
  /**
   * Execute the recorded sequence of actions on an object.
   * @param object $object Object.
   * @param bool $chain If true then the output of each call (only method calls)
   * will be used as the object of the next.
   * @return object The object or, if chaining is enabled, the last returned
   * object.
   */
  public function playMacro($object, $chain = true) {
    foreach ($this->actions as $action) {
      switch ($action[0]) {
        case 'call':
          $result = call_user_func_array(array($object, $action[1]), $action[2]);
          if ($chain)
            $object = $result;
          break;
        case 'set':
          $property = $action[1];
          $object->$property = $action[2];
          break;
        case 'unset':
          $property = $action[1];
          unset($object->$property);
          break;
        case 'offsetSet':
          if (isset($action[1]))
            $object[$action[1]] = $action[2];
          else
            $object[] = $action[2];
          break;
        case 'offsetUnset':
          unset($object[$action[1]]);
          break;
      }
    }
    return $object;
  }
}