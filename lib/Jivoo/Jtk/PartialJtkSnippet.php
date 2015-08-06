<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Snippets\Snippet;

/**
 * A toolkit snippet builder. All methods calls, getters, and setters are
 * redirected to the associated JTK settings object. To output the final snippet
 * invoke this object as a function.
 */
class PartialJtkSnippet {
  /**
   * @var JtkSnippet
   */
  private $snippet;
  
  /**
   * @var JtkObject
   */
  private $object;
  
  /**
   * Construct partial JTK snippet.
   * @param JtkSnippet $snippet Snippet object.
   * @param JtkObject $object Settings object.
   */
  public function __construct(JtkSnippet $snippet, JtkObject $object = null) {
    $this->snippet = $snippet;
    $this->object = $object;
    if (!isset($this->object))
      $this->object = $snippet->getObject();
  }
  
  /**
   * Call a method on the settings object.
   * @param string $method Method name.
   * @param miexed[] $parameters Method parameters.
   * @return mixed Return value.
   */
  public function __call($method, $parameters) {
    return call_user_func_array(array($this->object, $method), $parameters);
  }
  
  /**
   * Get the value of a property defined on the settings object. 
   * @param string $property Property name.
   * @return mixed Property value.
   */
  public function __get($property) {
    return $this->object->$property;
  }

  /**
   * Set the value of a property defined on the settings object.
   * @param string $property Property name.
   * @param mixed $property Property value.
   */
  public function __set($property, $value) {
    $this->object->$property = $value;
  }

  /**
   * Whether or not a property is ddefined on the settings object.
   * @param string $property Property name.
   * @return bool True if defined.
   */
  public function __isset($property) {
    return isset($this->object->$property);
  }

  /**
   * Unset a property defined on the settings object.
   * @param string $property Property name.
   */
  public function __unset($property) {
    unset($this->object->$property);
  }

  /**
   * Invoke the snippet using the settings object.
   * @return Jivoo\Routing\Response|string Response object or string.
   */
  public function __invoke() {
    return $this->snippet->__invoke(array($this->object));
  }
}