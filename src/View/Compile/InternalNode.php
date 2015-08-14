<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * An internal node that contains other nodes.
 */
class InternalNode extends TemplateNode implements \Countable {
  /**
   * @var TemplateNode[] Children.
   */
  protected $content = array();
  
  /**
   * Construct internal node.
   */
  public function __construct() {
    parent::__construct();
    $this->root = $this;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->content);
  }

  /**
   * Append a node.
   * @param TemplateNode $node Node.
   * @return self Self.
   */
  public function append(TemplateNode $node) {
    assume(!isset($node->parent));
    $node->parent = $this;
    $node->root = $this->root;
    if ($this->content !== array()) {
      $slice = array_slice($this->content, -1);
      $node->prev = $slice[0];
      $node->prev->next = $node;
    }
    $this->content[] = $node;
    return $this;
  }

  /**
   * Prepend a node.
   * @param TemplateNode $node Node.
   * @return self Self.
   */
  public function prepend(TemplateNode $node) {
    assume(!isset($node->parent));
    $node->parent = $this;
    $node->root = $this->root;
    if ($this->content !== array()) {
      $node->next = $this->content[0];
      $node->next->prev = $node;
    }
    $this->content = array_merge(array($node), $this->content);
    return $this;
  }

  /**
   * Remove a node.
   * @param TemplateNode $node Node.
   * @return self Self.
   */
  public function remove(TemplateNode $node) {
    assume($node->parent === $this);
    $this->content = array_diff($this->content, array($node));
    $node->parent = null;
    $node->root = null;
    if (isset($node->next))
      $node->next->prev = $node->prev;
    if (isset($node->prev))
      $node->prev->next = $node->next;
    $node->next = null;
    $node->prev = null;
    return $this;
  }

  /**
   * Insert a node before another node.
   * @param TemplateNode $node Node to insert.
   * @param TemplateNode $next Next node.
   * @return self Self.
   */
  public function insert(TemplateNode $node, TemplateNode $next) {
    assume($next->parent === $this);
    assume(!isset($node->parent));
    $offset = array_search($next, $this->content, true);
    array_splice($this->content, $offset, 0, array($node));
    $node->parent = $this;
    $node->root = $this->root;
    $node->next = $next;
    $node->prev = $next->prev;
    $next->prev = $node;
    if (isset($node->prev))
      $node->prev->next = $node;
    return $this;
  }

  /**
   * Replace a node with another node.
   * @param TemplateNode $node Node to replace.
   * @param TemplateNode $next Replacement node.
   * @return self Self.
   */
  public function replace(TemplateNode $node, TemplateNode $replacement) {
    assume($node->parent === $this);
    assume(!isset($replacement->parent));
    $offset = array_search($node, $this->content, true);
    $this->content[$offset] = $replacement;
    $replacement->parent = $this;
    $replacement->root = $this->root;
    $node->parent = null;
    $node->root = null;
    if (isset($node->next)) {
      $node->next->prev = $replacement;
      $replacement->next = $node->next;
    }
    if (isset($node->prev)) {
      $node->prev->next = $replacement;
      $replacement->prev = $node->prev;
    }
    $node->next = null;
    $node->prev = null;
    return $this;
  }

  /**
   * Remove all children.
   * @return self Self.
   */
  public function clear() {
    foreach ($this->content as $node) {
      $node->parent = null;
      $node->root = null;
      $node->next = null;
      $node->prev = null;
    }
    $this->content = array();
    return $this;
  }

  /**
   * Get children.
   * @return TemplateNode[] Array of children.
   */
  public function getChildren() {
    return $this->content;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $output = '';
    foreach ($this->content as $node)
      $output .= $node->__toString();
    return $output;
  }
}
