<?php
function utimer($start) {
  return round((microtime(true) - $start) * 1000000);
}

function test_function($rounds, $function) {
  $args = func_get_args();
  array_shift($args);
  array_shift($args);
  if (is_array($function)) {
    if (is_object($function[0])) {
      $functionName = get_class($function[0]);
    }
    else {
      $functionName = $function[0];
    }
    $functionName .= '::' . $function[1];
  }
  else {
    $functionName = $function;
  }
  echo $functionName . '(): ';
  $start = microtime(true);
  for ($i = 0; $i < $rounds; $i++) {
    $result = call_user_func_array($function, $args);
  }
  echo utimer($start) . ' µs' . PHP_EOL;
  return $result;
}

include '../app/essentials.php';

header('Content-Type:text/html;charset=utf-8');

class String implements arrayaccess {
  private $string = '';
  private $bytesize = -1;

  public function __construct($string = '') {
    if (is_array($string)) {
      $this->string = $string;
    }
    else if ($string instanceof self) {
      $this->bytesize = $string->bytesize;
      $this->string = $string->string;
    }
    else {
      $string = (string) $string;
      $this->bytesize = strlen($string);
      $this->string = preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
    }
  }

  public function __toString() {
    return $this->toString();
  }

  public function toString() {
    return join('', $this->string);
  }

  public function __get($property) {
    switch ($property) {
      case 'length':
      case 'size':
        return $this->getLength();
      case 'bytesize':
        return $this->getBytesize();
    }
  }

  public function offsetExists($offset) {
    return isset($this->string[$offset]);
  }

  public function offsetGet($offset) {
    return $this->charAt($offset);
  }

  public function offsetSet($offset, $value) {}

  public function offsetUnset($offset) {}

  public function charAt($offset) {
    if (!isset($this->string[$offset])) {
      return null;
    }
    else {
      return $this->slice($offset, 1);
    }
  }

  public function split($delimiter) {
    return explode((string) $delimiter, $this->toString());
  }

  public function concat($string) {
    return new self($this->toString() . (string) $string);
  }

  public function slice($start, $length = null) {
    return new self(array_slice($this->string, $start, $length));
  }

  public function count() {
    return $this->getLength();
  }

  public function getLength() {
    return count($this->string);
  }

  public function getBytesize() {
    if ($this->bytesize < 0) {
      $this->bytesize = strlen($this->__toString());
    }
    return $this->bytesize;
  }
}

abstract class UTF8 {
  public static function substr($string, $start, $length = null) {
    return join("",
      array_slice(preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY), $start,
        $length));
  }
}

$mbstring = "Rød grød med fløde";

echo '<pre>';

echo 'substr(): ';
var_dump(substr($mbstring, 0, 2));

echo 'mb_substr(): ';
var_dump(mb_substr($mbstring, 0, 2, 'UTF-8'));

echo 'mb_strcut(): ';
var_dump(mb_strcut($mbstring, 0, 2, 'UTF-8'));

echo 'UTF8::substr(): ';
var_dump(UTF8::substr($mbstring, 0, 2));

echo 'String::slice(): ';
$strobj = new String($mbstring);
$sliced = $strobj->slice(0, 2);
var_dump($sliced->toString());
echo 'length: ' . $sliced->length . PHP_EOL;
echo 'bytesize: ' . $sliced->bytesize . PHP_EOL;
echo 'bytesize: ' . $sliced[1] . PHP_EOL;
var_dump($split = $strobj->split($sliced[1]));
echo 'concat: ' . $strobj->concat(' og sød sukker') . PHP_EOL;

echo 'speed tests:' . PHP_EOL . PHP_EOL;

$rounds = 200;

test_function($rounds, 'substr', $mbstring, 0, 2);
test_function($rounds, 'mb_substr', $mbstring, 0, 2, 'UTF-8');
test_function($rounds, 'mb_strcut', $mbstring, 0, 2, 'UTF-8');
test_function($rounds, array('UTF8', 'substr'), $mbstring, 0, 2);
test_function($rounds, array($strobj, 'slice'), 0, 2);

echo '</pre>';
