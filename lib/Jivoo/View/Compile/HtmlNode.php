<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class HtmlNode extends InternalNode {
  private $tag = '';
  private $attributes = array();
  private $selfClosing = false;

  public function __construct($tag) {
    parent::__construct();
    $this->tag = $tag;
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