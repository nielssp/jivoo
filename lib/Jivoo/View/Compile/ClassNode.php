<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * A node for class-attributes.
 */
class ClassNode extends TemplateNode {
  /**
   * @var TemplateNode[].
   */
  private $classes = array();

  /**
   * Construct class node.
   * @param string|TemplateNode $class Class name or node.
   */
  public function __construct($class = null) {
    if (isset($class)) {
      if ($class instanceof TextNode) {
        $classes = explode(' ', $class->text);
        foreach ($classes as $class) {
          $class = trim($class);
          if ($class != '')
            $this->classes[$class] = $class;
        }
      }
      else {
        $this->classes[] = $class;
      }
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function isNull() {
    return count($this->classes) == 0;
  }
  
  /**
   * Add a class.
   * @param string|TemplateNode $class Class name or node.
   * @parma string|null Optional class name.
   */
  public function add($class, $name = null) {
    if (is_string($class) and !isset($name))
      $name = $class;
    if (isset($name))
      $this->classes[$name] = $class;
    else
      $this->classes[] = $class;
  }
  
  /**
   * Remove a class.
   * @param string $class Class name.
   */
  public function remove($class) {
    if (isset($this->classes[$class]))
      unset($this->classes[$class]);
  }
  
  /**
   * Toggle a class.
   * @param string $class Class name.
   */
  public function toggle($class) {
    if (isset($this->classes[$class]))
      unset($this->classes[$class]);
    else
      $this->classes[$class] = $class;
  }
  
  /**
   * Whether node has class.
   * @param string $class Class name.
   * @return bool True if node has class.
   */
  public function has($class) {
    return isset($this->classes[$class]);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $classes = array();
    foreach ($this->classes as $class) {
      if (is_string($class))
        $classes[] = trim($class);
      else
        $classes[] = trim($class->__toString());
    }
    return implode(' ', $classes);
  }
}