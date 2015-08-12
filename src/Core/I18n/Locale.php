<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\I18n;

use Jivoo\InvalidPropertyException;
use Jivoo\InvalidArgumentException;

/**
 * A localization, e.g. translation strings and date formats.
 * @property-read int $plurals Number of plural forms.
 * @property-read string $pluralExpr Plural expression (PHP).
 * @property-write string $pluralForms gettext-style C-expression for plurals,
 * e.g.: <code>nplurals=1; plural=(n!=1);</code>
 */
class Locale {
  /**
   * @var array Messages in english and their local translation.
   */
  protected $messages = array();
    
  /**
   * @var int
   */
  private $plurals = 2;
  
  /**
   * @var string
   */
  private $pluralExpr = 'return ($n != 1);';
  
  /**
   * @var string[]
   */
  protected static $properties = array(
    'name', 'localName', 'region', 'dateFormat', 'timeFormat',
    'dateTimeFormat', 'longFormat', 'decimalPoint', 'thousandsSep',
    'monthYear', 'monthDay', 'weekDay'
  );
  
  /**
   * @var string Language name (in English).
   */
  public $name = ''; // tr('[Locale::name]')

  /**
   * @var string Language name.
   */
  public $localName = ''; // tr('[Locale::localName]')
  
  /**
   * @var string Region name.
   */
  public $region = ''; // tr('[Locale::region]')

  /**
   * @var string Preferred date format.
   */
  public $dateFormat = 'Y-m-d'; // tr('[Locale::dateFormat]')
  
  /**
   * @var string Preferred time format.
   */
  public $timeFormat = 'H:i'; // tr('[Locale::timeFormat]')

  /**
   * @var string Preferred long date format.
   */
  public $dateTimeFormat = 'Y-m-d H:i'; // tr('[Locale::dateTimeFormat]')

  /**
   * @var string Preferred long date format.
   */
  public $longFormat = 'Y-m-d H:i'; // tr('[Locale::longFormat]')
  
  /**
   * @var string Month+year format
   */
  public $monthYear = 'F Y'; // tr('[Locale::monthYear]')
  
  /**
   * @var string Month+day format.
   */
  public $monthDay = 'F j'; // tr('[Locale::monthDay]')
  
  /**
   * @var string Week day+time format
   */
  public $weekDay = 'l H:i'; // tr('[Locale::weekDay]')
  
  /**
   * @var string Preferred decimal point.
   */
  public $decimalPoint = '.'; // tr('[Locale::decimalPoint]')

  /**
   * @var string Preferred thousands separator.
   */
  public $thousandsSep = ','; // tr('[Locale::thousandsSep]')

  /**
   * Construct new locale.
   * @param array $messages Optional messages to create locale from.
   */
  public function __construct($messages = array()) {
    $this->messages = $messages;
  }

  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    switch ($property) {
      case 'plurals':
      case 'pluralExpr':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Set value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'pluralForms':
        if (preg_match('/^nplurals *= *([0-9]+) *; *plural *=(.+);$/', trim($value), $matches) !== 1)
          throw new InvalidArgumentException('Invalid pluralForms format');
        $this->plurals = intval($matches[1]);
        $expr = self::convertExpr($matches[2]);
        $this->pluralExpr = 'return ' . $expr . ';';
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Converts simple gettext C expressions to PHP.
   * @param string $expr C expression.
   * @return string PHP expression.
   */
  public static function convertExpr($expr) {
    preg_match_all('/(==|!=|<=|>=|&&|\|\||<<|>>|[-!?:+*\/&|^~%<>()n]|[0-9]+)/', $expr, $matches);
    $tokens = $matches[0];
    $expr = '';
    $stack = array();
    foreach ($tokens as $token) {
      if ($token == 'n') {
        $expr .= '$n';
      }
      else if ($token == ':') {
        array_push($stack, ':');
        $expr .= ':(';
      }
      else if ($token == '(') {
        array_push($stack, '(');
        $expr .= '(';
      }
      else if ($token == ')') {
        while (count($stack) > 0) {
          $c = array_pop($stack);
          $expr .= ')';
          if ($c == '(')
            break;
        }
      }
      else {
        $expr .= $token;
      }
    }
    while (count($stack) > 0) {
      array_pop($stack);
      $expr .= ')';
    }
    return $expr;
  }

  /**
   * Unset value of a property.
   * @param string $property Property name.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __unset($property) {
    $this->__set($property, null);
  }

  /**
   * Extend this localization with additional messages from another one.
   * @param Locale $l Other localization object.
   */
  public function extend(Locale $l) {
    $this->messages = array_merge($this->messages, $l->messages);
    foreach (self::$properties as $property) {
      if ($l->hasProperty($property))
        $this->$property = $l->getProperty($property);
    }
  }

  /**
   * Set translation string.
   * @param string $message Message in english.
   * @param string|string[] $translation Translation string (or multiple
   * translation strings for plural forms.
   */
  public function set($messageId, $translation) {
    if (is_array($translation)) {
      $this->messages[$messageId] = $translation;
    }
    else {
      if (!isset($this->messages[$messageId]))
        $this->messages[$messageId] = array();
      $this->messages[$messageId][] = $translation;
    }
  }
  
  /**
   * Return a list of known messages along with translation strings and
   * pattern list.
   * @return string[][] List of arrays. The keys are messages. The first element
   * of each array array is the translation string, and the remaining elements
   * (if any) are the message variable patterns.
   * @deprecated
   */
  public function getTranslationStrings() {
    return $this->messages;
  }

  /**
   * Return a list of known messages along with translation strings.
   * @return string[][] List of arrays. The keys are message ids. The values are
   * lists of translations (e.g. one or n, where n is the number ofplural
   * forms).
   */
  public function getMessages() {
    return $this->messages;
  }
  
  /**
   * Translate a string.
   * @param string $message Message in english.
   * @param mixed $vars,... Values for placeholders starting from %1.
   * @return string Translated string.
   */
  public function get($message) {
    if (isset($this->messages[$message])) {
      $message = $this->messages[$message][0];
    }
    return $this->replacePlaceholders($message, array_slice(func_get_args(), 1));
  }
  
  public function hasProperty($property) {
    return isset($this->messages['[Locale::' . $property . ']'])
      and $this->messages['[Locale::' . $property . ']'][0] != ''
      and $this->messages['[Locale::' . $property . ']'][0] != '[Locale::' . $property . ']';
  }
  
  public function getProperty($property) {
    return $this->messages['[Locale::' . $property . ']'][0];
  }

  /**
   * Translate a string containing a numeric value, e.g.
   * <code>$l->nget('This post has %1 comments', 'This post has %1 comment', $numcomments);</code>
   * @param string $plural Message in english (plural).
   * @param string $singular Singular version of message in english.
   * @param int|array|\Countable $n The integer to test, replaces the
   * %1-placeholder in the message. May also be an array or a {@see \Countable},
   * in which case {@see count} will be called on the value.
   * @param mixed $vars,... Values for additional placholders starting from %2.
   * @return Translated string.
   * @throws \Jivoo\InvalidArgumentException If $n is not an integer, an 
   * array, or a {@see \Countable}. 
   */
  public function nget($plural, $singular, $n) {
    if (is_array($n) or $n instanceof \Countable)
      $n = count($n);
    assume(is_scalar($n));
    $n = intval($n);
    if (isset($this->messages[$plural])) {
      $i = intval(eval($this->pluralExpr));
      $message = $this->messages[$plural][0];
      if (isset($this->messages[$plural][$i]))
        $message = $this->messages[$plural][$i];
    }
    else if (abs($n) == 1) {
      $message = $singular;
    }
    else {
      $message = $plural;
    }
    return $this->replacePlaceholders($message, array_slice(func_get_args(), 2));
  }

  /**
   * Replace placeholders in a translation string.
   * @param string $message Translation string.
   * @param mixed[] $values Replacement values.
   * @return string Translation string after replacements.
   */
  public function replacePlaceholders($message, $values = array()) {
    $length = count($values);
    $i = 1;
    foreach ($values as $value) {
      if (is_array($value)) {
        $message = preg_replace_callback(
          '/%' . $i . '\{(.*?)\}\{(.*?)\}/',
          function($matches) use($value) {
            $length = count($value);
            $list = '';
            for ($i = 0; $i < $length; $i++) {
              $list .= $value[$i];
              if ($i != ($length - 1)) {
                if ($i == ($length - 2))
                  $list .= $matches[2];
                else
                  $list .= $matches[1];
              }
            }
            return $list;
          },
          $message
        );
      }
      else {
        $message = str_replace('%' . $i, $value, $message);
      }
      $i++;
    }
    return $message;
  }
  
  /**
   * Read a gettext PO-file.
   * @param string $file PO-file.
   * @return Locale Localization object.
   */
  public static function readPo($file) {
    $file = file($file, FILE_IGNORE_NEW_LINES);
    
    $messages = array();
    $message = array();
    $property = null;
    
    foreach ($file as $line) {
      $line = trim($line);
      if ($line == '')
        continue;
      if ($line[0] == '#')
        continue;
      if ($line[0] == '"') {
        if (!isset($property))
          continue;
        $message[$property] .= stripcslashes(substr($line, 1, -1));
        continue;
      }
      list($property, $msg) = explode(' ', $line, 2);
      if ($property == 'msgid') {
        if (count($message))
          $messages[] = $message;
        $message = array();
      }
      $message[$property] = stripcslashes(substr($msg, 1, -1));
    }
    if (count($message))
      $messages[] = $message;
    
    $l = new Locale();
    foreach ($messages as $message) {
      if (!isset($message['msgid']))
        continue;
      if ($message['msgid'] == '') {
        $properties = explode("\n", $message['msgstr']);
        foreach ($properties as $property) {
          list($property, $value) = explode(':', $property, 2);
          if (trim(strtolower($property)) == 'plural-forms') {
            $l->pluralForms = $value;
            break;
          }
        }
      }
      $id = $message['msgid'];
      if (isset($message['msgid_plural'])) {
        $id = $message['msgid_plural'];
        $plurals = array();
        foreach ($message as $property => $value) {
          if ($value != '' and strncmp($property, 'msgstr', 5) == 0)
            $plurals[intval(substr($property, 7, -1))] = $value;
        }
        if (count($plurals))
          $l->set($id, $plurals);
      }
      else if (isset($message['msgstr'])) {
        $message = $message['msgstr'];
        if ($message != '')
          $l->set($id, $message);
      }
    }
    foreach (self::$properties as $property) {
      if ($l->hasProperty($property))
        $l->$property = $l->getProperty($property);
    }
    return $l;
  }
  
  /**
   * Read a gettext MO-file.
   * @param string $file MO-file.
   * @return Locale Localization object.
   */
  public static function readMo($file) {
    $f = file_get_contents($file);
  
    if (!$f) {
      trigger_error('Could not open file: ' . $file, E_USER_ERROR);
      return null;
    }
  
    $magic = bin2hex(substr($f, 0, 4));
    if ($magic === '950412de') { // Big endian
      $header = 'Nrev/NN/NO/NT/NS/NH';
      $format = 'N';
    }
    else if ($magic === 'de120495') { // Little endian
      $header = 'Vrev/VN/VO/VT/VS/VH';
      $format = 'V';
    }
    else {
      trigger_error('Not a valid MO file: incorrect magic number: ' . $magic, E_USER_ERROR);
      return null;
    }
    $o = 4;
  
    $data = unpack($header, substr($f, $o, 24));
    $num = $data['N'];
    $oOffset = $data['O'];
    $tOffset = $data['T'];
    
    if ($num == 0)
      return new Locale();
    
    $format = $format . ($num * 2);
  
    $o = $oOffset;
    $oTable = unpack($format, substr($f, $o, 8 * $num));
    $o = $tOffset;
    $tTable = unpack($format, substr($f, $o, 8 * $num));
    
    $offsets = array();
    $n = $num * 2;
    $o = $oTable[2];
    $messages = array();
    for ($i = 1; $i <= $n; $i += 2) {
      $length = $oTable[$i];
      if ($length == 0) {
        $message = '';
        $o += 1;
      }
      else {
        $message = substr($f, $o, $length);
        $o += $length + 1;
        $hasNul = strpos($message, "\0");
        if ($hasNul !== false)
          $message = substr($message, $hasNul + 1); // gets plural
      }
      $messages[$i] = $message;
    }
    $o = $tTable[2];
    $l = new Locale();
    for ($i = 1; $i <= $n; $i += 2) {
      $length = $tTable[$i];
      if ($length > 0) {
        $translation = substr($f, $o, $length);
        $o += $length + 1;
        if ($messages[$i] == '') {
          $properties = explode("\n", $translation);
          foreach ($properties as $property) {
            list($property, $value) = explode(':', $property, 2);
            if (trim(strtolower($property)) == 'plural-forms') {
              $l->pluralForms = $value;
              break;
            }
          }
        }
        if (strpos($translation, "\0") !== false)
          $translation = explode("\0", $translation);
        $l->set($messages[$i], $translation);
      }
      else {
        $o += 1;
      }
    }
    foreach (self::$properties as $property) {
      if ($l->hasProperty($property))
        $l->$property = $l->getProperty($property);
    }
    return $l;
  }
}

