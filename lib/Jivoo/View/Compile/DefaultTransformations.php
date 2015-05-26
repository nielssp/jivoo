<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class DefaultTransformations {
  
  public static function getTransformations() {
    $functions = array(
      'outertext', 'innertext', 'text', 'if', 'else', 'foreach',
      'href', 'tr', 'tn' 
    );
    $transformations = array();
    foreach ($functions as $function)
      $transformations[$function] = array(
        'Jivoo\View\Compile\DefaultTransformations',
        '_' . $function
      );
    return $transformations;
  }
  
  static function _outertext(HtmlNode $node, $value) {
    $node->replaceWith(new PhpNode($value));
  }
  static function _innertext(HtmlNode $node, $value) {
    $node->clear()->append(new PhpNode($value));
  }
  static function _text(HtmlNode $node, $value) {
    self::_innertext($node, $value);
  }
  static function _if(HtmlNode $node, $value) {
    if (!isset($value)) {
      $prev = $node->prev;
      $between = array();
      do {
        if ($prev instanceof IfNode) {
          assume(count($prev->else) == 0);
          $between = array_reverse($between);
          foreach ($between as $betweenNode) {
            $betweenNode->detach();
            $prev->then->append($betweenNode);
          }
          $node->detach();
          $prev->then->append($node);
          return;
        }
        $between[] = $prev;
        $prev = $prev->prev;
      } while (isset($prev));
      throw new Exception('todo');
    }
    $ifNode = new IfNode($value);
    $node->replaceWith($ifNode);
    $ifNode->then->append($node);
  }
  static function _else(HtmlNode $node, $value) {
    $prev = $node->prev;
    $between = array();
    do {
      if ($prev instanceof IfNode) {
        $between = array_reverse($between);
        foreach ($between as $betweenNode) {
          $betweenNode->detach();
          $prev->then->append($betweenNode);
        }
        $node->detach();
        $prev->else->append($node);
        return;
      }
      $between[] = $prev;
      $prev = $prev->prev;
    } while (isset($prev));
    throw new Exception('todo');
  }
  static function _foreach(HtmlNode $node, $value) {
    if (!isset($value)) {
      if ($node->prev instanceof ForeachNode) {
        $foreachNode = $node->prev;
        $node->detach();
        $foreachNode->append($node);
        return;
      }
      throw new Exception('todo');
    }
    $foreachNode = new ForeachNode($value);
    $node->replaceWith($foreachNode);
    $foreachNode->append($node);
  }
  static function _href(HtmlNode $node, $value) {
    $node->setAttribute('href', new PhpNode('$this->link(' . $value . ')'));
  }
  static function _tr(HtmlNode $node, $value) {
    $translate = '';
    $num = 1;
    $params = array();
    $before = array();
    foreach ($node->getChildren() as $child) {
      if ($child instanceof TextNode) {
        $translate .= $child ->text;
      }
      else if ($child instanceof PhpNode and !$child->statement) {
        $translate .= '%' . $num;
        $params[] = $child->code;
        $num++;
      }
      else {
        throw new Exception('not implemented');
      }
    }
    if (count($params) == 0)
      $params = '';
    else
      $params = ', ' . implode(', ', $params);
    $translate = trim($translate);
    $node->clear();
    $phpNode = new PhpNode('tr(' . var_export($translate, true) . $params . ')');
    $node->append($phpNode);
  }
  static function _tn(HtmlNode $node, $value) {
    $translate = '';
    $num = 1;
    $params = array();
    $before = array();
    foreach ($node->getChildren() as $child) {
      if ($child instanceof TextNode) {
        $translate .= $child ->text;
      }
      else if ($child instanceof PhpNode and !$child->statement) {
        $translate .= '%' . $num;
        $params[] = $child->code;
        $num++;
      }
      else {
        throw new Exception('not implemented');
      }
    }
    if (count($params) == 0)
      $params = '';
    else
      $params = ', ' . implode(', ', $params);
    $translate = trim($translate);
    $node->clear();
    $phpNode = new PhpNode('tn(' . var_export($translate, true) . ', ' . var_export($value, true) . $params . ')');
    $node->append($phpNode);
  }
}
