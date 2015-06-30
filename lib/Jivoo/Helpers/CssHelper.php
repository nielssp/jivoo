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
   * @var callable[]
   */
  private $mixins = array();
  
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
      $this->blocks[$selector] = new CssBlock($selector, $this);
    return $this->blocks[$selector];
  }
  
  /**
   * Add a mixin function.
   * @param string $name Mixin name.
   * @param callable $callable Mixin function accepting one parameter: a
   * {@see CssBlock}.
   */
  public function addMixin($name, $callable) {
    $this->mixins[$name] = $callable;
  }
  
  /**
   * Get a mixin function.
   * @param string $name Mixin name.
   * @return callable Mixin function.
   */
  public function getMixin($name) {
    if (isset($this->mixins[$name]))
      return $this->mixins[$name];
    return null;
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
   * @var CssHelper
   */
  private $helper;
  
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
   * @param CssHelper $helper Helper object.
   */
  public function __construct($selector, CssHelper $helper) {
    $this->selector = $selector;
    $this->helper = $helper;
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
   * Apply a mixin (as defined on the {@see CssHelper}).
   * @param string $mixin Mixin name.
   * @return self Self.
   */
  public function apply($mixin) {
    $mixin = $this->helper->getMixin($mixin);
    $mixin($this);
    return $this;
  }
  
  /**
   * Create/edit a nested block.
   * @param string $selector CSS selector, '&' is automatically replaced with
   * the selector of the outer block.
   * @return CssBlock A block.
   */
  public function find($selector) {
    if (!isset($this->blocks[$selector])) {
      if (strpos($this->selector, ',') === false)
        $prefixes = array($this->selector);
      else
        $prefixes = explode(',', $this->selector);
      
      if (strpos($selector, ',') === false)
        $suffixes = array($selector);
      else
        $suffixes = explode(',', $selector);
      
      $selectors = array(); 
      foreach ($prefixes as $prefix) {
        foreach ($suffixes as $suffix) {
          if (strpos($suffix, '&') === false)
            $selectors[] = $prefix . ' ' . $suffix;
          else
            $selectors[] = str_replace('&', $prefix, $suffix);
        }
      }
      $this->blocks[$selector] = new CssBlock(implode(',', $selectors), $this->helper);
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
      else
        $out .= '/*' . $property . ':' . $value . ';' . '*/';
    }
    $out .= '}' . PHP_EOL;
    foreach ($this->blocks as $block)
      $out .= $block->__toString();
    return $out;
  }
}