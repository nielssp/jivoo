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
      'if', 'else', 'foreach',
      'tr', 'tn',
      'href', 'datetime', 'class', 'file',
      // attributes
      'src', 'alt', 'title', 'id', 'style'
    );
    $macros = array();
    foreach ($functions as $function)
      $macros[$function] = array($this, '_' . $function);
    return $macros;
  }
  
  /**
   * Automatic attribute macros.
   * @param string $attribute Attribute name prefixed with an underscore.
   * @param array $params Array of parameters, the first an {@see HtmlNode}, and
   * the second the string value of the macro (a string).
   */
  public function __call($attribute, $params) {
    $attribute = ltrim($attribute, '_');
    if (isset($params[1]))
      $params[0]->setAttribute($attribute, new PhpNode($params[1]));
  }

  /**
   * Replaces the node with PHP code.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _outerhtml(HtmlNode $node, $value) {
    $node->replaceWith(new PhpNode($value));
  }

  /**
   * Replaces the content of the node with PHP code.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _innerhtml(HtmlNode $node, $value) {
    $node->clear()->append(new PhpNode($value));
  }

  /**
   * Replaces the node with PHP code (with html entities replaced).
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _outertext(HtmlNode $node, $value) {
    $node->replaceWith(new PhpNode('h(' . $value . ')'));
  }

  /**
   * Replaces the content of the node with PHP code (with html entities replaced).
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _innertext(HtmlNode $node, $value) {
    $node->clear()->append(new PhpNode('h(' . $value . ')'));
  }

  /**
   * Replaces the content of the node with PHP code.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _html(HtmlNode $node, $value) {
    $this->_innerhtml($node, $value);
  }
  
  /**
   * Replaces the content of the node with PHP code (with html entities replaced).
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _text(HtmlNode $node, $value) {
    $this->_innertext($node, $value);
  }

  /**
   * Sets the primary (root) node.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (omit).
   */
  public function _main(HtmlNode $node, $value) {
  }

  /**
   * Replaces the content of the node with another template.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _embed(HtmlNode $node, $value) {
    $node->replaceWith(new PhpNode('$this->embed(' . var_export($value, true) . ')'));
  }

  /**
   * Replaces the content of the node with the content of a block.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _block(HtmlNode $node, $value) {
    $node->replaceWith(new PhpNode('$this->block(' . var_export($value, true) . ')'));
  }

  /**
   * Sets the layout.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _layout(HtmlNode $node, $value) {
    $node->before(new PhpNode('$this->layout(' . var_export($value, true) . ')', true));
  }

  /**
   * Disables the layout.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (omit).
   */
  public function _nolayout(HtmlNode $node, $value) {
    $node->before(new PhpNode('$this->disableLayout()', true));
  }

  /**
   * Sets the parent template.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  public function _extend(HtmlNode $node, $value) {
    $node->before(new PhpNode('$this->extend(' . var_export($value, true) . ')', true));
  }

  /**
   * Removes the node from the DOM.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (omit).
   */
  public function _ignore(HtmlNode $node, $value) {
    $node->detach();
  }

  /**
   * Begins or continues (if parameter omitted) an if block around the node.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _if(HtmlNode $node, $value) {
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
    $ifNode = new IfNode($value);
    $node->replaceWith($ifNode);
    $ifNode->then->append($node);
  }

  /**
   * Begins or continues (if parameter omitted) an else block around the node.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _else(HtmlNode $node, $value) {
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
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _foreach(HtmlNode $node, $value) {
    if (!isset($value)) {
      if ($node->prev instanceof ForeachNode) {
        $foreachNode = $node->prev;
        $node->detach();
        $foreachNode->append($node);
        return;
      }
      throw new InvalidTemplateException(tr('Empty foreach-node must folow another foreach-node'));
    }
    $foreachNode = new ForeachNode($value);
    $node->replaceWith($foreachNode);
    $foreachNode->append($node);
  }
  
  /**
   * Sets the datetime-attribute to the specified UNIX timestamp.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _datetime(HtmlNode $node, $value) {
    $node->setAttribute('datetime', new PhpNode('date(\'c\', ' . $value . ')'));
  }
  
  /**
   * Sets the href-attribute to the specified route-value (see {@see \Jivoo\Routing\Routing}).
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _href(HtmlNode $node, $value) {
    if ($node->hasAttribute('class')) {
      
    }
    else {
      $node->setAttribute('class', new PhpNode('if ($this->isCurrent(' . $value . ')) echo \'current\';', true));
    }
    $node->setAttribute('href', new PhpNode('$this->link(' . $value . ')'));
  }
  
  /**
   * Adds a class.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (PHP expression).
   */
  public function _class(HtmlNode $node, $value) {
    if ($node->hasAttribute('class')) {
      $node->setAttribute(
        'class',
        new PhpNode("'" . h($node->getAttribute('class')) . " ' . " . $value)
      );
    }
    else {
      $node->setAttribute('class', new PhpNode($value));
    }
  }

  /**
   * Points the src- or href-attribute at an asset.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (string).
   */
  static function _file(HtmlNode $node, $value) {
  }

  /**
   * Translates content of node, automatically replaces expressions with placeholders.
   * @param HtmlNode $node Node.
   * @param string|null $value Macro parameter (omit).
   */
  public function _tr(HtmlNode $node, $value) {
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
   * @param string|null $value Macro parameter (string).
   */
  public function _tn(HtmlNode $node, $value) {
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
    $phpNode = new PhpNode('tn(' . var_export($translate, true) . ', ' . var_export($value, true) . $params . ')');
    $node->append($phpNode);
  }
}
