<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

use Jivoo\Helpers\Html;

/**
 * An HTML node.
 * @property-read string $tag HTML tag.
 */
class HtmlNode extends InternalNode {
  /**
   * @var string HTML tag.
   */
  private $tag = '';
  
  /**
   * @var TemplateNode[] Atribute values.
   */
  private $attributes = array();
  
  /**
   * @var TemplateNode[] Data atribute values.
   */
  private $data = array();
  
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
    $this->selfClosing = Html::isSelfClosing($tag);
  }
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    if ($property == 'tag')
      return $this->tag;
    return parent::__get($property);
  }
  
  /**
   * Set value of data attribute.
   * @param string $attribute Data attribute name.
   * @param TemplateNode $value Value.
   */
  public function setData($attribute, TemplateNode $value = null) {
    $this->attributes['data-' . $attribute] = $value;
  }

  /**
   * Whether or not node has data attribute.
   * @param string $attribute Data attribute name.
   * @return boolean True if attribute is defined.
   */
  public function hasData($attribute) {
    return array_key_exists('data-' . $attribute, $this->attributes);
  }

  /**
   * Get value of data attribute.
   * @param string $attribute Data attribute name.
   * @return TemplateNode|null Value node or null if undefined.
   */
  public function getData($attribute) {
    if (isset($this->attributes['data-' . $attribute]))
      return $this->attributes['data-' . $attribute];
    return null;
  }

  /**
   * Remove a data attribute.
   * @param string $attribute Data attribute name.
   */
  public function removeData($attribute) {
    if (isset($this->attributes['data-' . $attribute]))
      unset($htis->attributes['data-' . $attribute]);
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
   * Add a class.
   * @param string|TemplateNode $class Class.
   */
  public function addClass($class) {
    if (!isset($this->attributes['class']))
      $this->attributes['class'] = new ClassNode();
    else
      $this->attributes['class'] = new ClassNode($this->attributes['class']);
    $this->attributes['class']->add($class);
  }
  
  /**
   * Remove a class.
   * @param string $class Class.
   */
  public function removeClass($class) {
    if (!isset($this->attributes['class']))
      return;
    if (!($this->attributes['class'] instanceof ClassNode))
      $this->attributes['class'] = new ClassNode($this->attributes['class']);
    $this->attributes['class']->remove($class);
  }
  
  /**
   * Toggle a class.
   * @param string $class Class.
   */
  public function toggleClass($class) {
    if (!isset($this->attributes['class']))
      return;
    if (!($this->attributes['class'] instanceof ClassNode))
      $this->attributes['class'] = new ClassNode($this->attributes['class']);
    $this->attributes['class']->toggle($class);
  }
  
  /**
   * Whether node has class.
   * @param string $class Class.
   * @return bool True if node has class.
   */
  public function hasClass($class) {
    if (!isset($this->attributes['class']))
      return false;
    if (!($this->attributes['class'] instanceof ClassNode))
      $this->attributes['class'] = new ClassNode($this->attributes['class']);
    return $this->attributes['class']->has($class);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $output = '<' . $this->tag;
    foreach ($this->attributes as $name => $value) {
      $output .= ' ' . $name;
      if (isset($value) and !$value->isNull())
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