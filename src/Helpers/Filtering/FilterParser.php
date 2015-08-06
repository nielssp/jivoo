<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

use Jivoo\Helpers\Filtering\Ast\StringNode;
use Jivoo\Helpers\Filtering\Ast\FilterNode;
use Jivoo\Helpers\Filtering\Ast\ComparisonNode;
use Jivoo\Helpers\Filtering\Ast\NotTermNode;

/**
 * A parser for filters.
 * 
 * Based on the following context-free grammar:
 * <code>
 * unichar     ::= any unicode character
 * stringchar  ::= unichar - ('"' | '\')
 * wordchar    ::= stringchar - (" " | "=" | "(" | ")" | "!" | "&" | "|" | "<" | ">")
 * reserved    ::= not | and | or
 * 
 * string      ::= '"' {stringchar | escape} '"'
 *               | word
 * word        ::= ((wordchar | escape) {wordchar | escape}) - reserved
 * escape      ::= "\" unichar
 * 
 * filter      ::= [notterm {[operator] notterm}]
 * notterm     ::= [not] term
 * term        ::= "(" filter ")"
 *               | string comparison string
 *               | string comparison "(" string {[operator] string} ")"
 *               | string
 * operator    ::= and | or
 * comparison  ::= "=" | "<" | ">" | "<=" | ">=" | "contains" | "on" | "at" | "in"
 * 
 * not         ::= "!" | "not"
 * and         ::= "&" | "and"
 * or          ::= "|" | "or"
 * </code>
 */
class FilterParser {
  /**
   * @var array[] Tokens.
   */
  private $tokens = array();
  
  /**
   * @var array Current token of the form array(type, value).
   */
  private $currentToken = null;
  
  /**
   * @var array Next token.
   */
  private $nextToken = null;
  
  /**
   * Parse a list of tokens.
   * @param array[] $tokens List of tokens as produced by {@see FilterScanner}.
   * @return \Jivoo\Helpers\Filtering\Ast\Node Abstract syntax tree.
   */
  public function parse($tokens) {
    $this->tokens = $tokens;
    if (isset($tokens[0])) {
      $this->nextToken = $tokens[0];
    }
    else {
      $this->nextToken = null;
    }
    $this->currentToken = null;
    return $this->parseFilter();
  }
  
  /**
   * Check type of next token.
   * @param string $type Expected type.
   * @return bool True if type matches.
   */
  private function is($type) {
    return $this->nextToken != null and $this->nextToken[0] == $type;
  }
  
  /**
   * Whether next token is a comparison operator.
   * @return bool True if comparison operator.
   */
  private function isComparisonOperator() {
    $type = $this->nextToken[0];
    return $this->nextToken != null and $type != 'string' and $type != '!' and
           $type != '&' and $type != '|' and $type != '(' and $type != ')';
  }
  
  /**
   * Accept a token.
   * @param string $type Optional token type.
   * @return bool True if successful.
   */
  private function accept($type = null) {
    if ($this->nextToken != null and ($type == null or $this->is($type))) {
      $this->pop();
      return true;
    }
    return false;
  }
  
  /**
   * Pop a token.
   * @return array Token.
   */
  private function pop() {
    $this->currentToken = array_shift($this->tokens);
    if (isset($this->tokens[0])) {
      $this->nextToken = $this->tokens[0];
    }
    else {
      $this->nextToken = null;
    }
    return $this->currentToken;
  }
  
  /**
   * Parse a filter.
   * @return \Jivoo\Helpers\Filtering\Ast\Node AST node.
   */
  private function parseFilter() {
    $node = new FilterNode();
    if ($this->nextToken != null) {
      $notTerm = $this->parseNotTerm();
      if ($notTerm == null) {
        return $node;
      }
      $node->children[] = $notTerm;
      while ($this->nextToken != null && !$this->is(')')) {
        // default operator is and
        $operator = 'and';
        if ($this->accept('|'))
          $operator = 'or';
        else
          $this->accept('&');
        $notTerm = $this->parseNotTerm();
        if ($notTerm == null)
          break;
        $notTerm->operator = $operator;
        $node->children[] = $notTerm;
      }
    }
    if (count($node->children) == 1) {
      return $node->children[0];
    }
    return $node;
  }
  
  /**
   * Parse a not term.
   * @return \Jivoo\Helpers\Filtering\Ast\Node AST node.
   */
  private function parseNotTerm() {
    if ($this->accept('!')) {
      return new NotTermNode($this->parseTerm());
    }
    return $this->parseTerm();
  }

  /**
   * Parse a term.
   * @return \Jivoo\Helpers\Filtering\Ast\Node AST node.
   */
  private function parseTerm() {
    if ($this->accept('(')) {
      $node = $this->parseFilter();
      $this->accept(')');
      return $node;
    }
    if (!$this->accept())
      return new StringNode('');
    $field = $this->currentToken[1];
    if ($this->isComparisonOperator()) {
      if (!$this->accept())
        return new StringNode($field);
      $comparison = $this->currentToken[0];
      if ($this->accept('(')) {
        $node = new FilterNode();
        if (!$this->accept()) {
          $node->children[] = new StringNode($field);
          $node->children[] = new StringNode($comparison);
          return $node;
        }
        $node->children[] = new ComparisonNode($field, $comparison, $this->currentToken[1]);
        while ($this->nextToken != null && !$this->is(')')) {
          $operator = 'and';
          if ($this->accept('|'))
            $operator = 'or';
          else
            $this->accept('&');
          if (!$this->accept()) {
            $node->children[] = new StringNode($operator);
            return $node;
          }
          $child = new ComparisonNode($field, $comparison, $this->currentToken[1]);
          $child->operator = $operator;
          $node->children[] = $child;
        }
        $this->accept(')');
        return $node;
      }
      if (!$this->accept())
        return new FilterNode(new StringNode($field), new StringNode($comparison));
      return new ComparisonNode($field, $comparison, $this->currentToken[1]);
    }
    return new StringNode($field);
  }
}

