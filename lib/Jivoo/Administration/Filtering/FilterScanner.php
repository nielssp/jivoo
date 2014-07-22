<?php
class FilterScanner {

  private $input = array();

  private $current = null;

  private static $reserved = '= ()!<>&|"';

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
        return $value;
      }
      $value .= $this->current;
      $this->pop();
    }
    $this->pop();
    return array('string', $value);
  }

  private function scanWord() {
    $value = '';
    while ($this->current != null and
           strpos(self::$reserved, $this->current) === false) {
            if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        break;
      }
      $value .= $this->current;
      $this->pop();
    }
    switch (strtolower($value)) {
      case 'and':
        return array('&', $value);
      case 'or':
        return array('|', $value);
      case 'not':
        return array('!', $value);
      case 'contains':
        return array('contains', $value);
      case 'before':
        return array('<', $value);
      case 'after':
        return array('>', $value);
      case 'in':
      case 'on':
      case 'at':
        return array('=', $value);
    }
    return array('string', $value);
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
    $value = $this->current;
    switch ($value) {
      case '(':
      case ')':
      case '=':
      case '|':
      case '&':
        $this->pop();
        return array($value, $value);
      case '<':
      case '>':
      case '!':
        $this->pop();
        if ($this->current == '=') {
          $this->pop();
          return array($value . '=', $value . '=');
        }
        return array($value, $value);
    }
    return $this->scanWord();
  }
}