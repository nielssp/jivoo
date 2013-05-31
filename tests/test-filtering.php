<?php

class FilterToken {
  const T_WORD = 1;
  const T_STRING = 2;
  const T_NOT = 3;
  const T_AND = 4;
  const T_OR = 5;
  const T_COLON = 6;
  const T_LPARENTHESIS = 7;
  const T_RPARENTHESIS = 8;

  public $type = 0;
  public $value = null;
  
  public function __construct($type, $value = null) {
    $this->type = $type;
    $this->value = $value;
  }
  
  public static function getType($type) {
    switch ($type) {
      case FilterToken::T_WORD:
        return 'T_WORD';
      case FilterToken::T_STRING:
        return 'T_STRING';
      case FilterToken::T_NOT:
        return 'T_NOT';
      case FilterToken::T_AND:
        return 'T_AND';
      case FilterToken::T_OR:
        return 'T_OR';
      case FilterToken::T_COLON:
        return 'T_COLON';
      case FilterToken::T_LPARENTHESIS:
        return 'T_LPARENTHESIS';
      case FilterToken::T_RPARENTHESIS:
        return 'T_RPARENTHESIS';
    }
    return 'T_UNKNOWN';
  }
}

class FilterScanner {
  
  private $input = array();
  private $current = null;
  
  private $reserved = ' :()!&|"';
  
  public function scan($input) {
    $this->input = str_split($input);
    $this->pop();
    $tokens = array();
    while (($token = $this->scanNext()) != null) {
      $tokens[] = $token;
    }
    return $tokens;
  }
  
  private function pop() {
    $this->current = array_shift($this->input);
    return $this->current;
  }
  
  private function isSpace() {
    return $this->current == ' ';
  }
  
  private function scanString() {
    $value = '';
    $this->pop();
    while ($this->current != '"') {
      if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        // Error: Missing " (ignore it)
        return new FilterToken(FilterToken::T_STRING, $value);
      }
      $value .= $this->current;
      $this->pop();
    }
    $this->pop();
    return new FilterToken(FilterToken::T_STRING, $value);
  }
  
  private function scanWord() {
    $value = '';
    while ($this->current != null AND strpos($this->reserved, $this->current) === false) {
      if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        break;
      }
      $value .= $this->current;
      $this->pop();
    }
    switch ($value) {
      case 'not':
        return new FilterToken(FilterToken::T_NOT);
      case 'and':
        return new FilterToken(FilterToken::T_AND);
      case 'or':
        return new FilterToken(FilterToken::T_OR);
      case 'NOT':
        return new FilterToken(FilterToken::T_NOT);
      case 'AND':
        return new FilterToken(FilterToken::T_AND);
      case 'OR':
        return new FilterToken(FilterToken::T_OR);
    }
    return new FilterToken(FilterToken::T_WORD, $value);
  }
  
  private function scanNext() {
    while ($this->isSpace()) {
      $this->pop();
    }
    if ($this->current == null) {
      return null;
    }
    if ($this->current == '"') {
      return $this->scanString();
    }
    switch ($this->current) {
      case ':':
        $this->pop();
        return new FilterToken(FilterToken::T_COLON);
      case '(':
        $this->pop();
        return new FilterToken(FilterToken::T_LPARENTHESIS);
      case ')':
        $this->pop();
        return new FilterToken(FilterToken::T_RPARENTHESIS);
      case '!':
        $this->pop();
        return new FilterToken(FilterToken::T_NOT);
      case '&':
        $this->pop();
        return new FilterToken(FilterToken::T_AND);
      case '|':
        $this->pop();
        return new FilterToken(FilterToken::T_OR);
    }
    return $this->scanWord();
  }
}

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

class ValueNode extends Node {
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
      while ($this->nextToken != null) {
        $operator = 'or';
        if ($this->accept(FilterToken::T_AND)) {
          $operator = 'and';
        }
        else {
          $this->accept(FilterToken::T_OR);
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
    if ($this->accept(FilterToken::T_WORD)) {
      $value = $this->currentToken->value;
      if ($this->accept(FilterToken::T_COLON)) {
        if (!$this->accept(FilterToken::T_WORD)) {
          $this->expect(FilterToken::T_STRING);
        }
        return new ComparisonNode($value, $this->currentToken->value);
      }
      return new ValueNode($value);
    }
    if ($this->accept(FilterToken::T_STRING)) {
      return new ValueNode($this->currentToken->value);
    }
    return null;
  }
}

abstract class FilterVisitor {
  protected abstract function visitFilter(FilterNode $node);
  protected abstract function visitNotTerm(NotTermNode $node);
  protected abstract function visitComparison(ComparisonNode $node);
  protected abstract function visitValue(ValueNode $node);
  public function visit(Node $node) {
    if ($node instanceof FilterNode) {
      return $this->visitFilter($node);
    }
    if ($node instanceof NotTermNode) {
      return $this->visitNotTerm($node);
    }
    if ($node instanceof ComparisonNode) {
      return $this->visitComparison($node);
    }
    if ($node instanceof ValueNode) {
      return $this->visitValue($node);
    }
    throw new Exception('Unknown node: ' . get_class($node));
  }
}

class OutputVisitor extends FilterVisitor {
  private $root = true;
  
  protected function visitFilter(FilterNode $node) {
    $root = false;
    $str = '';
    if ($this->root) {
      $root = true;
      $this->root = false;
    }
    else {
      $str .= '(';
    }
    foreach ($node->children as $child) {
      if ($child->operator != null) {
        $str .= $child->operator . ' ';
      }
      $str .= $this->visit($child) . ' ';
    }
    if ($root) {
      $this->root = true;
    }
    else {
      $str .= ')';
    }
    return $str;
  }
  protected function visitNotTerm(NotTermNode $node) {
    return 'not ' . $this->visit($node->child);
  }
  protected function visitComparison(ComparisonNode $node) {
    return $node->left . ':"' . $node->right . '"';
  }
  protected function visitValue(ValueNode $node) {
    return '"' . $node->value . '"';
  }
}

$input = $_GET['filter'];

$scanner = new FilterScanner();
$tokens = $scanner->scan($input);

echo 'Scanner result:<br/>';

foreach ($tokens as $token) {
  echo 'token ' . FilterToken::getType($token->type) . ': ' . $token->value . '<br/>';
}

$parser = new FilterParser();

$root = $parser->parse($tokens);

$visitor = new OutputVisitor();

echo 'PrettyPrinter result:<br/>';
echo $visitor->visit($root);

