<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

abstract class TemplateNode {
  private $macros = array();

  /**
   * @var InternalNode
  */
  protected $parent = null;

  protected $next = null;

  protected $prev = null;

  public function __construct() { }

  public function __get($property) {
    switch ($property) {
      case 'macros':
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
    return $this;
  }
  
  public function before(TemplateNode $node) {
    assume(isset($this->parent));
    $this->parent->insert($node, $this);
    return $this;
  }
  
  public function after(TemplateNode $node) {
    assume(isset($this->parent));
    if (isset($this->next))
      $this->next->before($node);
    else
      $this->parent->prepend($node);
    return $this;
  }

  public function replaceWith(TemplateNode $node) {
    assume(isset($this->parent));
    $this->parent->replace($this, $node);
    return $node;
  }

  public function addMacro($macro, $value = null) {
    $this->macros[$macro] = $value;
  }
}
