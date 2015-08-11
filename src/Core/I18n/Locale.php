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
 * @property string $name Language name (in English).
 * @property string $localName Language name.
 * @property string $region Region name.
 * @property string $dateFormat Preferred date format.
 * @property string $timeFormat Preferred time format.
 * @property string $longFormat Preferred long format, can use special
 * placeholders "%DATE" and "%TIME" to refer to already defined date and
 * time formats.
 * @property string $monthYear Month and year format.
 * @property string $monthDay Month and day format.
 * @property string $weekDay Week day and time format. 
 */
class Locale {
  /**
   * @var string
   */
  private $name = '';

  /**
   * @var string
   */
  private $localName = '';
  
  /**
   * @var string
   */
  private $region = '';
  
  /**
   * @var array Messages in english and their local translation.
   */
  private $messages = array();

  /**
   * @var string Preferred date format.
   */
  private $dateFormat = 'Y-m-d';
  
  /**
   * @var string Preferred time format.
   */
  private $timeFormat = 'H:i';

  /**
   * @var string Preferred long date format.
   */
  private $longFormat = '%DATE %TIME';
  
  /**
   * @var string Month+year format
   */
  private $monthYear = 'F Y';
  
  /**
   * @var string Month+day format.
   */
  private $monthDay = 'F j';
  
  /**
   * @var string Week day+time format
   */
  private $weekDay = 'l %TIME';
  
  /**
   * @var string
   */
  private $pluralForms = 'nplurals=2; plural=(n != 1);';
  
  /**
   * @var int
   */
  private $plurals = 2;
  
  /**
   * @var string
   */
  private $pluralExpr = 'return ($n != 1);';

  /**
   * Construct new localization.
   */
  public function __construct() { }

  /**
   * Get value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __get($property) {
    switch ($property) {
      case 'name':
      case 'localName':
      case 'region':
      case 'dateFormat':
      case 'timeFormat':
      case 'plurals':
      case 'pluralExpr':
        return $this->$property;
      case 'longFormat':
      case 'monthYear':
      case 'monthDay':
      case 'weekDay':
        return str_replace(
          array('%DATE', '%TIME'),
          array($this->dateFormat, $this->timeFormat),
          $this->$property
        );
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
      case 'name':
      case 'localName':
      case 'region':
      case 'dateFormat':
      case 'timeFormat':
      case 'longFormat':
      case 'monthYear':
      case 'monthDay':
      case 'weekDay':
        $this->$property = $value;
        return;
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
   * Whether or not a property is set, i.e. not null.
   * @param string $property Property name.
   * @return bool True if not null, false otherwise.
   * @throws InvalidPropertyException If property is not defined.
   */
  public function __isset($property) {
    switch ($property) {
      case 'dateFormat':
      case 'timeFormat':
      case 'longFormat':
      case 'monthYear':
      case 'monthDay':
      case 'weekDay':
        return isset($this->$property);
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Extend this localization with additional messages from another one.
   * @param Localization $l Other localization object.
   */
  public function extend(Locale $l) {
    $this->messages = array_merge($this->messages, $l->messages);
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
   */
  public function getTranslationStrings() {
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

  /**
   * Translate a string containing a numeric value, e.g.
   * <code>$l->getNumeric('This post has %1 comments', 'This post has %1 comment', $numcomments);</code>
   * @param string $plural Message in english (plural).
   * @param string $singular Singular version of message in english.
   * @param int|array $n The integer to test, replaces the %1-placeholder in the
   * message. 
   * @param mixed $vars,... Values for additional placholders starting from %2.
   * @return Translated string.
   */
  public function nget($plural, $singular, $n) {
    if (is_array($n))
      $n = count($n);
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
   * @return Localization Localization object.
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
    
    $l = new Localization();
    foreach ($messages as $message) {
      if (!isset($message['msgid']))
        continue;
      $id = $message['msgid'];
      if (isset($message['msgid_plural'])) {
        trigger_error(tr('plural not yet fully supported'), E_USER_NOTICE);
        $id = $message['msgid_plural'];
        if (isset($message['msgstr[1]'])) {
          $message = $message['msgstr[1]'];
          if ($message != '')
            $l->set($id, $message);
        }
      }
      else if (isset($message['msgstr'])) {
        $message = $message['msgstr'];
        if ($message != '')
          $l->set($id, $message);
      }
    }
    return $l;
  }
}

