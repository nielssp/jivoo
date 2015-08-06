<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\View\Compile\Macros;
use Jivoo\View\Compile\HtmlNode;
use Jivoo\View\Compile\PhpNode;
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
   * {@inheritdoc}
   */
  protected $properties = array('size', 'context');
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _block(HtmlNode $node, $value) {
    $block = new HtmlNode('div');
    if ($node->hasProperty('jtk:dialog'))
      $block->addClass('dialog');
    $block->addClass('block');
    if ($node->hasProperty('jtk:context'))
      $block->addClass('block-' . $node->getProperty('jtk:context'));
    $node->replaceWith($block);
    $block->append($node);
    $node->addClass('block-content');
    $header = null;
    $toolbar = null;
    $footer = null;
    foreach ($node->getChildren() as $child) {
      if ($child instanceof HtmlNode and $child->hasClass('header')) {
        $child->removeClass('header');
        $child->addClass('block-header');
        $header = $child;
      }
      if ($child instanceof HtmlNode and $child->hasClass('toolbar')) {
        $child->removeClass('toolbar');
        $child->addClass('block-toolbar');
        $toolbar = $child;
      }
      if ($child instanceof HtmlNode and $child->hasClass('footer')) {
        $child->removeClass('footer');
        $child->addClass('block-footer');
        $footer = $child;
      }
    }
    if (isset($header)) {
      $block->prepend($header->detach());
      if (isset($toolbar))
        $header->prepend($toolbar->detach());
    }
    if (isset($footer))
      $block->append($footer->detach());
  }
  
  /**
   * JTK block. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _toolbar(HtmlNode $node, $value) {
    $node->addClass('toolbar');
  }
  
  /**
   * JTK header. 
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
   * JTK footer. 
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
   * JTK grid cell. 
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
   * JTK grid. 
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _grid(HtmlNode $node, $value) {
    if (isset($value))
      $node->addClass('grid-' . str_replace(':', '-', $value));
    if ($node->hasProperty('jtk:size'))
      $node->addClass('grid-' . $node->getProperty('jtk:size'));
    else
      $node->addClass('grid');
    foreach ($node->getChildren() as $child) {
      if ($child instanceof HtmlNode)
        $child->addClass('cell');
    }
  }
  
  public function _button(HtmlNode $node, $value) {
    $node->addClass('button');
    if ($node->hasProperty('jtk:size'))
      $node->addClass('button-' . $node->getProperty('jtk:size'));
    if ($node->hasProperty('jtk:context'))
      $node->addClass('button-' . $node->getProperty('jtk:context'));
  }
  
  public function _icon(HtmlNode $node, $value) {
    $icon = new HtmlNode('span');
    $icon->addClass('icon');
    $icon->append(new PhpNode('$Icon->icon("' . $value . '")'));
    $node->prepend($icon);
  }
}
