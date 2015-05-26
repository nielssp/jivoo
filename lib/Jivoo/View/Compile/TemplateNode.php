<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

abstract class TemplateNode {
  private $transformations = array();

  /**
   * @var InternalNode
  */
  protected $parent = null;

  protected $next = null;

  protected $prev = null;

  public function __construct() { }

  public function __get($property) {
    switch ($property) {
      case 'transformations':
      case 'parent':
      case 'next':
      case 'prev':
        return $this->$property;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  public function __isset($property) {
    return $this->__get($property) !== null;
  }

  public function detach() {
    assume(isset($this->parent));
    $this->parent->remove($this);
  }

  public function replaceWith(TemplateNode $node) {
    assume(isset($this->parent));
    $this->parent->replace($this, $node);
  }

  public function addTransformation($transformation, $value = null) {
    $this->transformations[$transformation] = $value;
  }
}
