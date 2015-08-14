<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

use Jivoo\InvalidPropertyException;

/**
 * A template node.
 * @property-read string[] $macros Macros and values.
 * @property-read InternalNode|null $parent Parent node if any.
 * @property-read InternalNode|null $root Root node if any.
 * @property-read TemplateNode|null $next Next sibling if any.
 * @property-read TemplateNode|null $prev Previous sibling if any.
 */
abstract class TemplateNode {
  /**
   * @var (TextNode|PhpNode|null)[] Macros and values.
   */
  private $macros = array();
  
  /**
   * @var string[]
   */
  private $properties = array();
  
  /**
   * @var InternalNode|null Root node if any.
   */
  protected $root = null;

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
      case 'root':
      case 'next':
      case 'prev':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
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
   * Whether node is null.
   * @return boolean True if null.
   */
  public function isNull() {
    return false;
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
   * Get value of a property (or macro if one exists with the same name).
   * @param string $property Property.
   * @return string Value.
   */
  public function getProperty($property) {
    if (isset($this->macros[$property])) {
      $this->properties[$property] = $this->macros[$property];
      unset($this->macros[$property]);
    }
    if (!isset($this->properties[$property]))
      return null;
    return $this->properties[$property];
  }

  /**
   * Get value of a property (removes macro with same name if it exists).
   * @param string $property Property.
   * @param string $value Value.
   */
  public function setProperty($property, $value = null) {
    if (isset($this->macros[$property]))
      unset($this->macros[$property]);
    $this->properties[$property] = $value;
  }

  /**
   * Whether a property exists (or macro if one exists with the same name).
   * @param string $property Property.
   * @return bool True if property exists.
   */
  public function hasProperty($property) {
    if (array_key_exists($property, $this->macros)) {
      $this->properties[$property] = $this->macros[$property];
      unset($this->macros[$property]);
    }
    return array_key_exists($property, $this->properties);
  }

  /**
   * Remove a property (removes macro with same name if it exists).
   * @param string $property Property.
   */
  public function removeProperty($property) {
    if (array_key_exists($property, $this->macros))
      unset($this->macros[$property]);
    if (array_key_exists($property, $this->properties))
      unset($this->properties[$property]);
  }

  /**
   * Add a macro.
   * @param string $macro Macro name.
   * @param TextNode|PhpNode|null $value Macro parameter if any.
   */
  public function addMacro($macro, TemplateNode $value = null) {
    $this->macros[$macro] = $value;
  }
  
  /**
   * Whether node has macro.
   * @param string $macro Macro name.
   * @return bool True if node has macro.
   */
  public function hasMacro($macro) {
    return array_key_exists($macro, $this->macros);
  }
  
  /**
   * Get parameter of a macro, returns null if macro doesn't hava a parameter,
   * use {@see hasMacro} to check for existence of macro.
   * @param string $macro Macro name.
   * @return TextNode|PhpNode|null Macro parameter.
   */
  public function getMacro($macro) {
    if (isset($this->macros[$macro]))
      return $this->macros[$macro];
    return null;
  }
  
  /**
   * Remove a macro.
   * @param string $macro Macro name.
   */
  public function removeMacro($macro) {
    if (isset($this->macros[$macro]))
      unset($this->macros[$macro]);
  }
}
