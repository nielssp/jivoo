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
   * @var array[]
   */
  private $colors = array();
  
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
   * @param callable $callable Mixin function accepting one or more parameters:
   * the first one a {@see CssBlock}.
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
    if (!isset($this->colors[$hex])) {
      if (strlen($hex) == 3) {
        $rgb = str_split($hex, 1);
        $rgb[0] .= $rgb[0]; 
        $rgb[1] .= $rgb[1];
        $rgb[2] .= $rgb[2];
      }
      else {
        $rgb = str_split($hex, 2);
      }
      $this->colors[$hex] = $this->rgb(intval($rgb[0], 16), intval($rgb[1], 16), intval($rgb[2], 16));
    }
    return $this->colors[$hex]; 
  }
  
  /**
   * Converts RGB to an HSL-array which can be used for color manipulation.
   * @param int|float $r Red: An integer between 0 and 100 or a float between
   * 0.0 and 1.0. 
   * @param int|float $g Green: An integer between 0 and 100 or a float between
   * 0.0 and 1.0.
   * @param int|float $b Blue: An integer between 0 and 100 or a float between
   * 0.0 and 1.0.
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
    if ($L != 0 and $L != 1)
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
   * between 0.0 and 1.0.
   * @param int|float $l Ligthness: An integer between 0 and 100 or a float
   * between 0.0 and 1.0.
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
   * Get CSS-output for color.
   * @param array $color Color tuple.
   * @return string CSS color.
   */
  public function toString($color) {
    if (is_string($color))
      return $color;
    $h = $color[0];
    $s = round($color[1] * 100);
    $l = round($color[2] * 100);
    return 'hsl(' . $h . ', ' . $s . '%, ' . $l . '%)';
  }

  /**
   * Get hue of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @return int Hue in degrees.
   */
  public function hue($color) {
    if (is_string($color))
      $color = $this->hex($color);
    return $color[0];
  }

  /**
   * Get saturation (in HSL) of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @return float Saturation as a float between 0.0. and 1.0.
   */
  public function saturation($color) {
    if (is_string($color))
      $color = $this->hex($color);
    return $color[1];
  }

  /**
   * Get lightness of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @return float Lightness as a float between 0.0. and 1.0.
   */
  public function lightness($color) {
    if (is_string($color))
      $color = $this->hex($color);
    return $color[2];
  }
  

  /**
   * Get RGB components for a color..
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @return float[] RGB color.
   */
  public function toRgb($color) {
    if (is_string($color))
      $color = $this->hex($color);
    $h = $color[0];
    $s = $color[1];
    $l = $color[2];
    if ($s == 0)
      return array($l, $l, $l);
    $C = (1 - abs(2 * $l - 1)) * $s;
    $H = $h / 60;
    $m = $l - 0.5 * $C;
    $X = $C * (1 - abs(fmod($H, 2) - 1)) + $m;
    $C += $m;
    if ($H < 1)
      return array($C, $X, $m);
    if ($H < 2)
      return array($X, $C, $m);
    if ($H < 3)
      return array($m, $C, $X);
    if ($H < 4)
      return array($m, $X, $C);
    if ($H < 5)
      return array($X, $m, $C);
    return array($C, $m, $X);
  }

  /**
   * Get perceived brightness of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @return float Luminance as a float between 0.0. and 1.0.
   */
  public function luminance($color) {
    // from http://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef
    list($r, $g, $b) = $this->toRgb($color);
    $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
    $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
    $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
  }
  
  /**
   * Set hue of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int $hue New hue in degrees.
   * @return array Color.
   */
  public function setHue($color, $hue) {
    if (is_string($color))
      $color = $this->hex($color);
    $color[0] = $hue;
    return $color;
  }
  
  /**
   * Set saturation of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $s Saturation: An integer between 0 and 100 or a float
   * between 0.0 and 1.0.
   * @return array Color.
   */
  public function setSaturation($color, $s) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($s))
      $s /= 100;
    $color[1] = $s;
    return $color;
  }
  
  /**
   * Set lightness of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $l Lightness: An integer between 0 and 100 or a float
   * between 0.0 and 1.0.
   * @return array Color.
   */
  public function setLightness($color, $l) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($l))
      $l /= 100;
    $color[2] = $l;
    return $color;
  }
  
  /**
   * Adjust hue of a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int $amount Amount to increase (or decrease) hue by in degrees.
   * @return array Color.
   */
  public function adjustHue($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    $color[0] = ($color[0] + $amount) % 360;
    return $color;
  }
  
  /**
   * Saturate a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $amount Amount to increase saturation by.
   * @return array Color.
   */
  public function saturate($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($amount))
      $amount /= 100;
    $color[1] += $amount;
    if ($color[1] > 1) $color[1] = 1.0;
    return $color;
  }
  
  /**
   * Desaturate a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $amount Amount to decrease saturation by.
   * @return array Color.
   */
  public function desaturate($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($amount))
      $amount /= 100;
    $color[1] -= $amount;
    if ($color[1] < 0) $color[1] = 0.0;
    return $color;
  }
  
  /**
   * Ligthen a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $amount Amount to increase ligthness by.
   * @return array Color.
   */
  public function lighten($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($amount))
      $amount /= 100;
    $color[2] += $amount;
    if ($color[2] > 1) $color[2] = 1.0;
    return $color;
  }
  
  /**
   * Darken a color.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $amount Amount to decrease ligthness by.
   * @return array Color.
   */
  public function darken($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($amount))
      $amount /= 100;
    $color[2] -= $amount;
    if ($color[2] < 0) $color[2] = 0.0;
    return $color;
  }
  
  /**
   * Darken a color by mixing with black.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $amount Amount to darken by.
   * @return array Color.
   */
  public function shade($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($amount))
      $amount /= 100;
    $color[1] *= 1 - $amount;
    $color[2] *= 1 - $amount;
    if ($color[1] < 0) $color[1] = 0.0;
    if ($color[2] < 0) $color[2] = 0.0;
    return $color;
  }
  
  /**
   * Lighten a color by mixing with white.
   * @param string|array $color Color tuple, e.g. from {@see rgb()}.
   * @param int|float $amount Amount to lighten by.
   * @return array Color.
   */
  public function tint($color, $amount) {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_int($amount))
      $amount /= 100;
    $color[1] *= 1 - $amount;
    $color[2] += (1 - $color[2]) * $amount;
    if ($color[1] < 0) $color[1] = 0.0;
    if ($color[2] > 1) $color[2] = 1.0;
    return $color;
  }
  
  /**
   * Returns the light or dark color depending on the relative luminance of the
   * background color. 
   * @param string|array $color Background color, e.g. from {@see rgb()}.
   * @param string|array $dark Dark color, e.g. from {@see rgb()}.
   * @param string|array $light Lightcolor, e.g. from {@see rgb()}.
   * @return array Color.
   */
  public function contrasted($color, $dark = '#000', $light = '#fff') {
    if (is_string($color))
      $color = $this->hex($color);
    if (is_string($dark))
      $dark = $this->hex($dark);
    if (is_string($light))
      $light = $this->hex($light);
    $lum1 = $this->luminance($color);
    $lum2 = $this->luminance($dark);
    $lum3 = $this->luminance($light);
    if (abs($lum1 - $lum2) > abs($lum1 - $lum3))
      return $dark;
    else
      return $light;
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
    $args = func_get_args();
    array_shift($args);
    array_unshift($args, $this);
    call_user_func_array($mixin, $args);
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
        if (is_array($value))
          $value = $this->helper->toString($value);
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