<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

/*
unichar     ::= any unicode character
stringchar  ::= unichar - ('"' | '\')
wordchar    ::= stringchar - (" " | "=" | "(" | ")" | "!" | "&" | "|" | "<" | ">")
reserved    ::= not | and | or

string      ::= '"' {stringchar | escape} '"'
              | word
word        ::= ((wordchar | escape) {wordchar | escape}) - reserved
escape      ::= "\" unichar

filter      ::= [notterm {[operator] notterm}]
notterm     ::= [not] term
term        ::= "(" filter ")"
              | string comparison string
              | string comparison "(" string {[operator] string} ")"
              | string
operator    ::= and | or
comparison  ::= "=" | "<" | ">" | "<=" | ">=" | "contains" | "on" | "at" | "in"

not         ::= "!" | "not"
and         ::= "&" | "and"
or          ::= "|" | "or"
*/
abstract class Node {
  public $operator = '';
}

class FilterNode extends Node {
  public $children = array();

  public function __construct() {
    $this->children = func_get_args();
  }
}

class NotTermNode extends Node {
  public $child;

  public function __construct(Node $child) {
    $this->child = $child;
  }
}

class StringNode extends Node {
  public $value = '';

  public function __construct($value) {
    $this->value = $value;
  }
}

class ComparisonNode extends Node {
  public $left = '';
  public $comparison = '';
  public $right = '';

  public function __construct($left, $comparison, $right) {
    $this->left = $left;
    $this->comparison = $comparison;
    $this->right = $right;
  }
}

class FilterParser {

  private $tokens = array();
  private $currentToken = null;
  private $nextToken = null;
  
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
  
  private function is($type) {
    return $this->nextToken != null and $this->nextToken[0] == $type;
  }
  
  private function isComparisonOperator() {
    $type = $this->nextToken[0];
    return $this->nextToken != null and $type != 'string' and $type != '!' and
           $type != '&' and $type != '|' and $type != '(' and $type != ')';
  }
  
  private function accept($type = null) {
    if ($this->nextToken != null and ($type == null or $this->is($type))) {
      $this->pop();
      return true;
    }
    return false;
  }
  
  private function expect($type = null) {
    if ($this->accept($type)) {
      return $this->currentToken;
    }
    throw new \Exception(
      'Parse error: Unexpected token "' . $this->nextToken[0]
      . '" expected "' . $type . '"');
  }
  
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
  
  private function parseNotTerm() {
    if ($this->accept('!')) {
      return new NotTermNode($this->parseTerm());
    }
    return $this->parseTerm();
  }
  
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