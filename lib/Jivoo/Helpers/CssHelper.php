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
   * @var bool
   */
  private $useRgbOutput = false;
  
  /**
   * Whether to clear rules after converting to string with {@see __toString()}. 
   * @param string $clear Clears if true.
   */
  public function clearAfterPrint($clear = true) {
    $this->clearAfterPrint = $clear;
  }
  

  /**
   * Whether to convert color values to RGB (for older browsers).
   * @param bool $useRgbOutput Converts all colors to RGB if true.
   */
  public function useRgbOutput($useRgbOutput = true) {
    $this->useRgbOutput = $useRgbOutput;
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
  
  /**
   * Convert a hexadecimal RGB representation to an HSL-array.
   * @param string $hex Hexadecimal color string, e.g. '#fff', '#aabbcc', or
   * '112233'.
   * @return array A 3-tuple of hue, saturation and lightness.
   */
  public function hex($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
      $rgb = str_split($hex, 1);
    }
    else {
      $rgb = str_split($hex, 2);
    }
    return $this->rgb(intval($rgb[0], 16), intval($rgb[1], 16), intval($rgb[2], 16));
  }
  
  /**
   * Converts RGB to an HSL-array which can be used for color manipulation.
   * @param int|float $r Red: An integer between 0 and 100 or a float between
   * 0.0 and 1.1. 
   * @param int|float $g Green: An integer between 0 and 100 or a float between
   * 0.0 and 1.1.
   * @param int|float $b Blue: An integer between 0 and 100 or a float between
   * 0.0 and 1.1.
   * @return array A 3-tuple of hue, saturation and lightness.
   */
  public function rgb($r, $g, $b) {
    if (is_int($r)) {
      $r /= 255;
      $g /= 255;
      $b /= 255;
    }
    $M = max($r, $g, $b);
    $m = min($r, $g, $b);
    $C = $M - $m;
    $L = 0.5 * ($M + $m);
    $H = 0.0;
    $S = 0.0;
    if ($C != 0) {
      if ($M == $r)
        $H = ($g - $b) / $C;
      if ($M == $g)
        $H = ($b - $r) / $C + 2;
      if ($M == $b)
        $H = ($r - $g) / $C + 4;
    }
    if ($L != 0)
      $S = $C / (1 - abs(2 * $L - 1));
    $h = round(60 * $H);
    if ($h < 0) $h += 360;
    if ($h >= 360) $h -= 360;
    return array($h, $S, $L);
  }
  
  /**
   * Create a color tuple from HSL values.
   * @param int $h Hue: An integer between 0 and 360.
   * @param int|float $s Saturation: An integer between 0 and 100 or a float
   * between 0.0 and 1.1.
   * @param int|float $l Ligthness: An integer between 0 and 100 or a float
   * between 0.0 and 1.1.
   * @return multitype:unknown number
   */
  public function hsl($h, $s, $l) {
    if (is_int($s))
      $s /= 100;
    if (is_int($l))
      $l /= 100;
    return array($h, $s, $l);
  }
  
  /**
   * Ligthen a color.
   * @param array $color Color tuple, e.g. from {@see rgb()}.
   * @param float $amount Amount to increase ligthness by.
   * @return array Color.
   */
  public function lighten($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    $color[2] *= 1 + $amount;
    if ($color[2] > 1) $color[2] = 1.0;
    return $color;
  }
  
  /**
   * Darken a color.
   * @param array $color Color tuple, e.g. from {@see rgb()}.
   * @param float $amount Amount to decrease ligthness by.
   * @return array Color.
   */
  public function darken($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    $color[2] *= 1 - $amount;
    if ($color[2] < 0) $color[2] = 0.0;
    return $color;
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
   * @param string|array $value Value.
   */
  public function __set($property, $value) {
    $this->declarations[Utilities::camelCaseToDashes($property)] = $value;
  }

  /**
   * Declaration getter.
   * @param string $property Property in camelCase, e.g. backgroundColor,
   * fontFamily, etc.
   * @return string|array Value.
   */
  public function __get($property) {
    $property = Utilities::camelCaseToDashes($property);
    if (isset($this->declarations[$property]))
      return $this->declarations[$property];
    return null;
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
      if (isset($value)) {
        if (is_array($value)) {
          $h = $value[0];
          $s = round($value[1] * 100);
          $l = round($value[2] * 100);
          $value = 'hsl(' . $h . ', ' . $s . '%, ' . $l . '%)';
        }
        $out .= $property . ':' . $value . ';';
      }
      else {
        $out .= '/*' . $property . ' has no value */';
      }
    }
    $out .= '}' . PHP_EOL;
    foreach ($this->blocks as $block)
      $out .= $block->__toString();
    return $out;
  }
}