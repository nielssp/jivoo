<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class HtmlNode extends InternalNode {
  /**
   * HTML5 tags that should not be closed.
   *
   * Source: http://xahlee.info/js/html5_non-closing_tag.html
   * @var array Associative array of lowercase tag-names and true-values.
   */
  private static $selfClosingTags = array('area' => true, 'base' => true,
    'br' => true, 'col' => true, 'command' => true, 'embed' => true,
    'hr' => true, 'img' => true, 'input' => true, 'keygen' => true,
    'link' => true, 'meta' => true, 'param' => true, 'source' => true,
    'track' => true, 'wbr' => true
  );
  
  private $tag = '';
  private $attributes = array();
  private $selfClosing = false;

  public function __construct($tag) {
    parent::__construct();
    $this->tag = $tag;
    $this->selfClosing = isset(self::$selfClosingTags[$tag]);
  }

  public function setAttribute($attribute, TemplateNode $value = null) {
    $this->attributes[$attribute] = $value;
  }

  public function hasAttribute($attribute) {
    return array_key_exists($attribute, $this->attributes);
  }

  public function getAttribute($attribute) {
    if (isset($this->attributes[$attribute]))
      return $this->attributes[$attribute];
    return null;
  }

  public function removeAttribute($attribute) {
    if (isset($this->attributes[$attribute]))
      unset($htis->attributes[$attribute]);
  }

  public function __toString() {
    $output = '<' . $this->tag;
    foreach ($this->attributes as $name => $value) {
      $output .= ' ' . $name;
      if (isset($value))
        $output .= '="' . $value . '"';
    }
    if (count($this->content) == 0 and $this->selfClosing)
      return $output . ' />';
    $output .= '>';
    $output .= parent::__toString();
    $output .= '</' . $this->tag . '>';
    return $output;
  }
}