<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

use Jivoo\View\InvalidTemplateException;

/**
 * Implements the default template macros.
 */
class DefaultMacros extends Macros {
  
  /**
   * Get an associative array of macro names and functions.
   * @return callable[] Associative array mapping macro names to callables.
   */
  public function getMacros() {
    $functions = array(
      'outerhtml', 'innerhtml', 'outertext', 'innertext', 'html', 'text',
      'main', 'embed', 'block', 'layout', 'nolayout', 'extend', 'ignore',
      'import', 'imports',
      'if', 'else', 'foreach',
      'tr', 'tn',
      'href', 'datetime', 'class', 'file',
    );
    $macros = array();
    foreach ($functions as $function)
      $macros[$function] = array($this, '_' . $function);
    return $macros;
  }

  /**
   * Replaces the node with PHP code.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _outerhtml(HtmlNode $node, TemplateNode $value) {
    $node->replaceWith($value);
  }

  /**
   * Replaces the content of the node with PHP code.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _innerhtml(HtmlNode $node, TemplateNode $value) {
    $node->clear()->append($value);
  }

  /**
   * Replaces the node with PHP code (with html entities replaced).
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _outertext(HtmlNode $node, TemplateNode $value) {
    if ($value instanceof PhpNode)
      $value = new PhpNode('h(' . $value->code . ')');
    $node->replaceWith($value);
  }

  /**
   * Replaces the content of the node with PHP code (with html entities replaced).
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _innertext(HtmlNode $node, TemplateNode $value) {
    if ($value instanceof PhpNode)
      $value = new PhpNode('h(' . $value->code . ')');
    $node->clear()->append($value);
  }

  /**
   * Replaces the content of the node with PHP code.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _html(HtmlNode $node, TemplateNode $value) {
    $this->_innerhtml($node, $value);
  }
  
  /**
   * Replaces the content of the node with PHP code (with html entities replaced).
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _text(HtmlNode $node, TemplateNode $value) {
    $this->_innertext($node, $value);
  }

  /**
   * Sets the primary (root) node.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _main(HtmlNode $node, TemplateNode $value = null) {
  }

  /**
   * Import a styleheet or script. If the current node is a '<link />' it is
   * removed.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _import(HtmlNode $node, TemplateNode $value) {
    $node->root->prepend(new PhpNode('$this->import(' . PhpNode::expr($value)->code . ')', true));
    if ($node->tag == 'link')
      $node->detach();
  }

  /**
   * Replaces node with list of script and/or stylesheet imports.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _imports(HtmlNode $node, TemplateNode $value = null) {
    if (isset($value)) {
    $node->root->prepend(new PhpNode('$this->import(' . PhpNode::expr($value)->code . ')', true));
    }
    if ($node->tag == 'link')
      $node->replaceWith(new PhpNode('$this->resourceBlock()'));
  }

  /**
   * Replaces the content of the node with another template.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _embed(HtmlNode $node, TemplateNode $value) {
    $node->replaceWith(new PhpNode('$this->embed(' . PhpNode::expr($value)->code . ')'));
  }

  /**
   * Replaces the content of the node with the content of a block.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _block(HtmlNode $node, TemplateNode $value) {
    $node->replaceWith(new PhpNode('$this->block(' . PhpNode::expr($value)->code . ')'));
  }

  /**
   * Sets the layout.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _layout(HtmlNode $node, TemplateNode $value) {
    $node->before(new PhpNode('$this->layout(' . PhpNode::expr($value)->code . ')', true));
  }

  /**
   * Disables the layout.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _nolayout(HtmlNode $node, TemplateNode $value = null) {
    $node->before(new PhpNode('$this->disableLayout()', true));
  }

  /**
   * Sets the parent template.
   * @param HtmlNode $node Node.
   * @param TemplateNode $value Macro parameter.
   */
  public function _extend(HtmlNode $node, TemplateNode $value) {
    $node->before(new PhpNode('$this->extend(' . PhpNode::expr($value)->code . ')', true));
  }

  /**
   * Removes the node from the DOM.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _ignore(HtmlNode $node, TemplateNode $value = null) {
    $node->detach();
  }

  /**
   * Begins or continues (if parameter omitted) an if block around the node.
   * @param HtmlNode $node Node.
   * @param TemplateNode $value Macro parameter.
   */
  public function _if(HtmlNode $node, TemplateNode $value) {
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
      throw new InvalidTemplateException(tr('Empty if-node must follow another if-node.'));
    }
    $ifNode = new IfNode(PhpNode::expr($value)->code);
    $node->replaceWith($ifNode);
    $ifNode->then->append($node);
  }

  /**
   * Begins or continues (if parameter omitted) an else block around the node.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _else(HtmlNode $node, TemplateNode $value = null) {
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
    throw new InvalidTemplateException(tr('Else-node must follow an if-node or another else-node.'));
  }

  /**
   * Begins or continues (if parameter omitted) a foreach block around the node.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _foreach(HtmlNode $node, TemplateNode $value) {
    if (!isset($value)) {
      if ($node->prev instanceof ForeachNode) {
        $foreachNode = $node->prev;
        $node->detach();
        $foreachNode->append($node);
        return;
      }
      throw new InvalidTemplateException(tr('Empty foreach-node must folow another foreach-node'));
    }
    $foreachNode = new ForeachNode(PhpNode::expr($value)->code);
    $node->replaceWith($foreachNode);
    $foreachNode->append($node);
  }
  
  /**
   * Sets the datetime-attribute to the specified UNIX timestamp.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _datetime(HtmlNode $node, TemplateNode $value) {
    $node->setAttribute('datetime', new PhpNode('date(\'c\', ' . PhpNode::expr($value)->code . ')'));
  }
  
  /**
   * Sets the href-attribute to the specified route-value (see {@see \Jivoo\Routing\Routing}).
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _href(HtmlNode $node, TemplateNode $value) {
    if ($node->hasAttribute('class')) {
      
    }
    else {
      $node->setAttribute('class', new PhpNode('if ($this->isCurrent(' . PhpNode::expr($value)->code . ')) echo \'current\';', true));
    }
    $node->setAttribute('href', new PhpNode('$this->link(' . PhpNode::expr($value)->code . ')'));
  }
  
  /**
   * Adds a class.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _class(HtmlNode $node, TemplateNode $value) {
    if ($node->hasAttribute('class')) {
      $node->setAttribute(
        'class',
        new PhpNode("'" . h($node->getAttribute('class')) . " ' . " . PhpNode::expr($value)->code)
      );
    }
    else {
      $node->setAttribute('class', PhpNode::expr($value)->code);
    }
  }

  /**
   * Points the src- or href-attribute at an asset.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  static function _file(HtmlNode $node, TemplateNode $value = null) {
    // TODO: implement
  }

  /**
   * Translates content of node, automatically replaces expressions with placeholders.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _tr(HtmlNode $node, TemplateNode $value = null) {
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
        throw new InvalidTemplateException('not implemented');
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

  /**
   * Translates content of node, automatically replaces expressions with placeholders.
   * Expects content of node to be plural and macro parameter to be singular.
   * @param HtmlNode $node Node.
   * @param TemplateNode|null $value Macro parameter.
   */
  public function _tn(HtmlNode $node, TemplateNode $value) {
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
        throw new InvalidTemplateException('not implemented');
      }
    }
    if (count($params) == 0)
      $params = '';
    else
      $params = ', ' . implode(', ', $params);
    $translate = trim($translate);
    $node->clear();
    $phpNode = new PhpNode('tn(' . var_export($translate, true) . ', ' . PhpNode::expr($value)->code . $params . ')');
    $node->append($phpNode);
  }
}
