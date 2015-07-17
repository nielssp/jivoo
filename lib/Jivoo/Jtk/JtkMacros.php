<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\View\Compile\Macros;
use Jivoo\View\Compile\HtmlNode;
use Jivoo\View\Compile\TextNode;
use Jivoo\View\Compile\Jivoo\View\Compile;

/**
 * JTK template macros (for compiled templates).
 */
class JtkMacros extends Macros {
  /**
   * {@inheritdoc}
   */
  protected $namespace = 'jtk';
  
  /**
   * Set JTK size 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _size(HtmlNode $node, $value) {
    $node->setData('jtk-size', new TextNode($value));
  }
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _block(HtmlNode $node, $value) {
    $block = new HtmlNode('div');
    $block->addClass('block');
    $node->replaceWith($block);
    $block->append($node);
    $node->addClass('block-content');
    foreach ($node->getChildren() as $child) {
      if ($child instanceof HtmlNode and $child->hasClass('header')) {
        $child->removeClass('header');
        $child->addClass('block-header');
        $block->prepend($child->detach());
      }
      if ($child instanceof HtmlNode and $child->hasClass('footer')) {
        $child->removeClass('footer');
        $child->addClass('block-footer');
        $block->append($child->detach());
      }
    }
  }
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _header(HtmlNode $node, $value) {
    $header = new HtmlNode('div');
    $header->addClass('header');
    $node->replaceWith($header);
    $header->append($node);
  }
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _footer(HtmlNode $node, $value) {
    $footer = new HtmlNode('div');
    $footer->addClass('footer');
    $node->replaceWith($footer);
    $footer->append($node);
  }
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _cell(HtmlNode $node, $value) {
    $cell = new HtmlNode('div');
    $cell->addClass('cell');
    $node->replaceWith($cell);
    $cell->append($node);
  }
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _grid(HtmlNode $node, $value) {
    if (isset($value))
      $node->addClass('grid-' . str_replace(':', '-', $value));
    else
      $node->addClass('grid');
    if ($node->hasData('jtk-size')) {
      $node->addClass('grid-' . $node->getData('jtk-size'));
      $node->removeData('jtk-size');
    }
    else if ($node->hasMacro('jtk:size')) {
      $node->addClass('grid-' . $node->getMacro('jtk:size'));
      $node->removeMacro('jtk:size');
    }
    foreach ($node->getChildren() as $child) {
      if ($child instanceof HtmlNode)
        $child->addClass('cell');
    }
  }
}
