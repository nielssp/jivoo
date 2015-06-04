<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * An HTML node.
 */
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
  
  /**
   * @var string HTML tag.
   */
  private $tag = '';
  
  /**
   * @var TemplateNode[] Atribute values.
   */
  private $attributes = array();
  
  /**
   * @var bool If tag is self closing.
   */
  private $selfClosing = false;

  /**
   * Construct HTML node.
   * @param string $tag HTML tag.
   */
  public function __construct($tag) {
    parent::__construct();
    $this->tag = $tag;
    $this->selfClosing = isset(self::$selfClosingTags[$tag]);
  }

  /**
   * Set value of attribute.
   * @param string $attribute Attribute name.
   * @param TemplateNode $value Value.
   */
  public function setAttribute($attribute, TemplateNode $value = null) {
    $this->attributes[$attribute] = $value;
  }

  /**
   * Whether or not node has attribute.
   * @param string $attribute Attribute name.
   * @return boolean True if attribute is defined.
   */
  public function hasAttribute($attribute) {
    return array_key_exists($attribute, $this->attributes);
  }

  /**
   * Get value of attribute.
   * @param string $attribute Attribute name.
   * @return TemplateNode|null Value node or null if undefined.
   */
  public function getAttribute($attribute) {
    if (isset($this->attributes[$attribute]))
      return $this->attributes[$attribute];
    return null;
  }

  /**
   * Remove an attribute.
   * @param string $attribute Attribute name.
   */
  public function removeAttribute($attribute) {
    if (isset($this->attributes[$attribute]))
      unset($htis->attributes[$attribute]);
  }

  /**
   * {@inheritdoc}
   */
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