<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class InternalNode extends TemplateNode implements \Countable {
  protected $content = array();

  public function count() {
    return count($this->content);
  }

  public function append(TemplateNode $node) {
    assume(!isset($node->parent));
    $node->parent = $this;
    if ($this->content !== array()) {
      $slice = array_slice($this->content, -1);
      $node->prev = $slice[0];
      $node->prev->next = $node;
    }
    $this->content[] = $node;
    return $this;
  }

  public function prepend(TemplateNode $node) {
    assume(!isset($node->parent));
    $node->parent = $this;
    if ($this->content !== array()) {
      $node->next = $this->content[0];
      $node->next->prev = $node;
    }
    $this->content = array_merge(array($node), $this->content);
    return $this;
  }

  public function remove(TemplateNode $node) {
    assume($node->parent === $this);
    $this->content = array_diff($this->content, array($node));
    $node->parent = null;
    if (isset($node->next))
      $node->next->prev = $node->prev;
    if (isset($node->prev))
      $node->prev->next = $node->next;
    $node->next = null;
    $node->prev = null;
    return $this;
  }
  
  public function insert(TemplateNode $node, TemplateNode $next) {
    assume($next->parent === $this);
    assume(!isset($node->parent));
    $offset = array_search($next, $this->content, true);
    array_splice($this->content, $offset, 0, array($node));
    $node->parent = $this;
    $node->next = $next;
    $node->prev = $next->prev;
    $next->prev = $node;
    if (isset($node->prev))
      $node->prev->next = $node;
    return $this;
  }

  public function replace(TemplateNode $node, TemplateNode $replacement) {
    assume($node->parent === $this);
    assume(!isset($replacement->parent));
    $offset = array_search($node, $this->content, true);
    $this->content[$offset] = $replacement;
    $node->parent = null;
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

  public function clear() {
    foreach ($this->content as $node) {
      $node->parent = null;
      $node->next = null;
      $node->prev = null;
    }
    $this->content = array();
    return $this;
  }

  public function getChildren() {
    return $this->content;
  }

  public function __toString() {
    $output = '';
    foreach ($this->content as $node)
      $output .= $node->__toString();
    return $output;
  }
}
