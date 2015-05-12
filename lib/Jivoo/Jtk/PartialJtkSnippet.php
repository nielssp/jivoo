<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Lib;

/**
 * A toolkit snippet builder.
 */
class PartialJtkSnippet {
  private $snippet;
  private $object;
  public function __construct(JtkSnippet $snippet, JtkObject $object = null) {
    $this->snippet = $snippet;
    $this->object = $object;
    if (!isset($this->object))
      $this->object = $snippet->getObject();
  }
  
  public function __call($method, $parameters) {
    return call_user_func_array(array($this->object, $method), $parameters);
  }
  
  public function __get($property) {
    return $this->object->$property;
  }
  
  public function __set($property, $value) {
    $this->object->$property = $value;
  }
  
  public function __isset($property) {
    return isset($this->object->$property);
  }
  
  public function __unset($property) {
    unset($this->object->$property);
  }
  
  public function __invoke() {
    return $this->snippet->__invoke(array($this->object));
  }
}