<?php
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
  public $right = '';

  public function __construct($left, $right) {
    $this->left = $left;
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

  private function lookAhead($type) {
    return $this->nextToken != null && $this->nextToken->type == $type;
  }

  private function accept($type) {
    if ($this->lookAhead($type)) {
      $this->pop();
      return true;
    }
    return false;
  }

  private function expect($type) {
    if ($this->accept($type)) {
      return $this->currentToken;
    }
    throw new Exception('Parse error: Unexpected token '
      . FilterToken::getType($this->nextToken->type) . ', expected '
      . FilterToken::getType($type));
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
      while ($this->nextToken != null && !$this->lookAhead(FilterToken::T_RPARENTHESIS)) {
        // default operator is and
        $operator = 'and';
        if ($this->accept(FilterToken::T_OR)) {
          $operator = 'or';
        }
        else {
          $this->accept(FilterToken::T_AND);
        }
        $notTerm = $this->parseNotTerm();
        if ($notTerm == null) {
          break;
        }
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
    if ($this->accept(FilterToken::T_NOT)) {
      return new NotTermNode($this->parseTerm());
    }
    return $this->parseTerm();
  }

  private function parseTerm() {
    if ($this->accept(FilterToken::T_LPARENTHESIS)) {
      $node = $this->parseFilter();
      $this->expect(FilterToken::T_RPARENTHESIS);
      return $node;
    }
    $this->expect(FilterToken::T_STRING);
    $value = $this->currentToken->value;
    if ($this->accept(FilterToken::T_EQUALS)) {
      $this->expect(FilterToken::T_STRING);
      return new ComparisonNode($value, $this->currentToken->value);
    }
    return new StringNode($value);
  }
}