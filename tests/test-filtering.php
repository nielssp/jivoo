<?php

class FilterToken {
  const T_STRING = 1;
  const T_NOT = 2;
  const T_AND = 3;
  const T_OR = 4;
  const T_EQUALS = 5;
  const T_LPARENTHESIS = 6;
  const T_RPARENTHESIS = 7;

  public $type = 0;
  public $value = null;
  
  public function __construct($type, $value = null) {
    $this->type = $type;
    $this->value = $value;
  }
  
  public static function getType($type) {
    switch ($type) {
      case FilterToken::T_STRING:
        return 'T_STRING';
      case FilterToken::T_NOT:
        return 'T_NOT';
      case FilterToken::T_AND:
        return 'T_AND';
      case FilterToken::T_OR:
        return 'T_OR';
      case FilterToken::T_EQUALS:
        return 'T_EQUALS';
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
  
  private static $reserved = '= ()!&|"';
  
  public function scan($input) {
    $this->input = str_split($input);
    $this->pop();
    $tokens = array();
    while (($token = $this->scanNext()) != null) {
      $tokens[] = $token;
    }
    return $tokens;
  }
  
  public static function getEqualsOperator() {
    return self::$reserved[0];
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
    while ($this->current != null AND strpos(self::$reserved, $this->current) === false) {
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
    return new FilterToken(FilterToken::T_STRING, $value);
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
      case self::$reserved[0]:
        $this->pop();
        return new FilterToken(FilterToken::T_EQUALS);
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

abstract class FilterVisitor {
  protected abstract function visitFilter(FilterNode $node);
  protected abstract function visitNotTerm(NotTermNode $node);
  protected abstract function visitComparison(ComparisonNode $node);
  protected abstract function visitString(StringNode $node);
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
    if ($node instanceof StringNode) {
      return $this->visitString($node);
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
    return '"' . addcslashes($node->left, '\\"')
      . '" ' . FilterScanner::getEqualsOperator() . ' "'
      . addcslashes($node->right, '\\"') . '"';
  }
  protected function visitString(StringNode $node) {
    return '"' . addcslashes($node->value, '\\"') . '"';
  }
}

class QueryVisitor extends FilterVisitor {
  
//   private $query;
  
//   public function __construct(ICondition $query) {
//     $this->query = $query;
//   }
  
  protected function visitFilter(FilterNode $node) {
    $condition = new Condition();
    foreach ($node->children as $child) {
      if ($child->operator == 'or') {
        $condition->orWhere($this->visit($child));
      }
      else {
        $condition->andWhere($this->visit($child));
      }
    }
    return $condition;
  }
  protected function visitNotTerm(NotTermNode $node) {
    // new NotCondition()...
  }
  protected function visitComparison(ComparisonNode $node) {
    /// @TODO check for existence of column
    return new Condition($node->left . ' = ?', $node->right);
  }
  protected function visitString(StringNode $node) {
    // Foreach search column add condition
  }
}

include '../../LAB/LabTest.php';

$input = 'author = root and something (foo | bar | baz)';

$test = new LabTest('Filtering');

include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core/Helpers');


$scanner = new FilterScanner();

$rounds = 100;

$tokens = $test->testFunction($rounds, array($scanner, 'scan'), $input);
$test->dumpResult();

// $test->puts('Scanner result:' . PHP_EOL);

// foreach ($tokens as $token) {
//   $test->puts(FilterToken::getType($token->type) . ': ' . $token->value . PHP_EOL);
// }

$parser = new FilterParser();

$root = $test->testFunction($rounds, array($parser, 'parse'), $tokens);
$test->dumpResult();

// $visitor = new OutputVisitor();

// echo 'PrettyPrinter result:<br/>';
// echo $visitor->visit($root);

$test->report();

