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
    if (!isset($blocks[$selector]))
      $blocks[$selector] = new CssBlock();
    return $blocks[$selector];
  }

  /**
   * Convert to CSS code.
   * @return string CSS.
   */
  public function __toString() {
    $out = '';
    foreach ($this->blocks as $selector => $block) {
      $out .= $selector . '{' . $block->__toString() . '}' . PHP_EOL;
    }
    return $out;
  }
}

class CssBlock {
  /**
   * @var string[]
   */
  private $declarations = array();
  
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
    $out = '';
    foreach ($this->declarations as $property => $value) {
      $out .= $property . ':' . $value . ';';
    }
    return $out;
  }
}