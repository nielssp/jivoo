<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\Utilities;

/**
 * CSS helper.
 */
class CssHelper extends Helper {
  /**
   * @var CssBlock[]
   */
  private $blocks = array();
  
  /**
   * @var bool
   */
  private $clearAfterPrint = true;
  
  /**
   * Whether to clear rules after converting to string with {@see __toString()}. 
   * @param string $clear Clears if true.
   */
  public function clearAfterPrint($clear = true) {
    $this->clearAfterPrint = $clear;
  }
  
  /**
   * Create block.
   * @param string $selector CSS selector.
   * @return CssBlock A block.
   */
  public function __invoke($selector) {
    return $this->select($selector);
  }

  /**
   * Create block.
   * @param string $selector CSS selector.
   * @return CssBlock A block.
   */
  public function select($selector) {
    if (!isset($this->blocks[$selector]))
      $this->blocks[$selector] = new CssBlock($selector);
    return $this->blocks[$selector];
  }
  
  /**
   * Clear rules.
   */
  public function clear() {
    $this->blocks = array();
  }

  /**
   * Convert to CSS code.
   * @return string CSS.
   */
  public function __toString() {
    $out = '';
    foreach ($this->blocks as $block)
      $out .= $block->__toString();
    if ($this->clearAfterPrint)
      $this->clear();
    return $out;
  }
}

/**
 * A CSS block.
 */
class CssBlock {
  /**
   * @var string
   */
  private $selector;
  
  /**
   * @var string[]
   */
  private $declarations = array();

  /**
   * @var CssBlock[]
   */
  private $blocks = array();
  
  /**
   * Construct CSS block.
   * @param string $selector CSS selector.
   */
  public function __construct($selector) {
    $this->selector = $selector;
  }
  
  /**
   * Declaration setter.
   * @param string $property Property in camelCase, e.g. backgroundColor,
   * fontFamily, etc.
   * @param string $value Value.
   */
  public function __set($property, $value) {
    $this->declarations[Utilities::camelCaseToDashes($property)] = $value;
  }
  
  /**
   * Create/edit a nested block.
   * @param string $selector CSS selector, '&' is automatically replaced with
   * the selector of the outer block.
   * @return CssBlock A block.
   */
  public function __invoke($selector) {
    return $this->find($selector);
  }
  
  /**
   * Create/edit a nested block.
   * @param string $selector CSS selector, '&' is automatically replaced with
   * the selector of the outer block.
   * @return CssBlock A block.
   */
  public function find($selector) {
    if (!isset($this->blocks[$selector])) {
      $this->blocks[$selector] = new CssBlock(
        str_replace('&', $this->selector, $selector)
      );
    }
    return $this->blocks[$selector];
  }
  
  /**
   * Nest selectors using a callback (e.g. an anonymous function).
   * @param callable $callable Function that accepts a single parameter, a
   * {@see CssBlock} (this block).
   * @return self Self.
   */
  public function nest($callable) {
    $callable($this);
    return $this;
  }
  
  /**
   * Add one or more declarations.
   * @param string|string $property Property in lisp-case,
   * e.g. background-color., font-family, etc. Or an associative array of
   * declarations.
   * @param string $value Value.
   * @return self Self.
   */
  public function css($property, $value = null) {
    if (is_array($property)) {
      foreach ($property as $p => $value)
        $this->declarations[$p] = $value;
    }
    else {
      $this->declarations[$property] = $value;
    }
    return $this;
  }
  
  /**
   * Convert block to CSS.
   * @return string CSS.
   */
  public function __toString() {
    $out = $this->selector . '{';
    foreach ($this->declarations as $property => $value) {
      if (isset($value))
        $out .= $property . ':' . $value . ';';
    }
    $out .= '}' . PHP_EOL;
    foreach ($this->blocks as $block)
      $out .= $block->__toString();
    return $out;
  }
}