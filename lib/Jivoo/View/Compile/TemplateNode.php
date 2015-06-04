<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * A template node.
 */
abstract class TemplateNode {
  /**
   * @var string[] Macros and values.
   */
  private $macros = array();

  /**
   * @var InternalNode|null Parent node if any.
  */
  protected $parent = null;

  /**
   * @var TemplateNode|null Next sibling if any.
   */
  protected $next = null;

  /**
   * @var TemplateNode|null Previous sibling if any.
   */
  protected $prev = null;

  /**
   * Construct template node.
   */
  public function __construct() { }

  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
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

  /**
   * Whether or not a property is set, i.e. not null.
   * @param string $property Property name.
   * @return bool True if not null, false otherwise.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __isset($property) {
    return $this->__get($property) !== null;
  }

  /**
   * Detach from parent.
   * @return self Self.
   */
  public function detach() {
    assume(isset($this->parent));
    $this->parent->remove($this);
    return $this;
  }
  
  /**
   * Insert a node before this one.
   * @param TemplateNode $node Node to insert.
   * @return self Self.
   */
  public function before(TemplateNode $node) {
    assume(isset($this->parent));
    $this->parent->insert($node, $this);
    return $this;
  }

  /**
   * Insert a node after this one.
   * @param TemplateNode $node Node to insert.
   * @return self Self.
   */
  public function after(TemplateNode $node) {
    assume(isset($this->parent));
    if (isset($this->next))
      $this->next->before($node);
    else
      $this->parent->prepend($node);
    return $this;
  }

  /**
   * Replace this node with another.
   * @param TemplateNode $node Replacement node.
   * @return TemplateNode Replacement node.
   */
  public function replaceWith(TemplateNode $node) {
    assume(isset($this->parent));
    $this->parent->replace($this, $node);
    return $node;
  }

  /**
   * Add a macro.
   * @param string $macro Macro name.
   * @param string|null $value Macro parameter if any.
   */
  public function addMacro($macro, $value = null) {
    $this->macros[$macro] = $value;
  }
}
