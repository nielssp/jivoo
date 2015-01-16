<?php
/**
 * A localization, e.g. translation strings and date formats.
 * @package Jivoo\Core
 */
class Localization {
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
      case 'dateFormat':
      case 'timeFormat':
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
      case 'dateFormat':
      case 'timeFormat':
      case 'longFormat':
      case 'monthYear':
      case 'monthDay':
      case 'weekDay':
        $this->$property = $value;
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
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
  public function extend(Localization $l) {
    $this->messages = array_merge($this->messages, $l->messages);
  }

  /**
   * Set translation string.
   * @param string $message Message in english.
   * @param string $translation Translation string.
   * @param string $patterns,... Regular expression patterns to match message variables
   * against.
   */
  public function set($message, $translation) {
    $args = func_get_args();
    array_shift($args);
    if (!isset($this->messages[$message]))
      $this->messages[$message] = array();
    $this->messages[$message][] = $args;
  }

  /**
   * Translate a string.
   * @param string $message Message in english.
   * @param mixed $vars,... Values for placeholders starting from %1.
   * @return string Translated string.
   */
  public function get($message) {
    $args = func_get_args();
    $args = array_slice($args, 1);
    if (isset($this->messages[$message])) {
      $patterns = $this->messages[$message];
      foreach ($patterns as $pattern) {
        $translation = array_shift($pattern);
        $patternLength = count($pattern);
        $match = true;
        for ($i = 0; $i < $patternLength; $i++) {
          if (!isset($pattern[$i]))
            continue;
          $arg = $args[$i];
          if (is_array($arg))
            $arg = count($arg);
          if (preg_match($pattern[$i], $arg) !== 1) {
            $match = false;
            break;
          }
        }
        if ($match)
          return $this->replacePlaceholders($translation, $args);
      }
    }
    return $this->replacePlaceholders($message, $args);
  }

  /**
   * Translate a string containing a numeric value, e.g.
   * <code>$l->getNumeric('This post has %1 comments', 'This post has %1 comment', $numcomments);</code>
   * @param string $message Message in english (plural).
   * @param string $singular Singular version of message in english.
   * @param mixed $vars,... Values for placholders starting from %1, the first one (%1) is the
   * numeral to test.
   * @return Translated string.
   */
  public function getNumeric($message, $singular) {
    $args = func_get_args();
    $args = array_slice($args, 2);
    if (isset($this->messages[$message]))
      return call_user_func_array(array($this, 'get'), array_merge(array($message), $args));
    $num = $args[0];
    if (is_array($num))
      $num = count($num);
    if (abs($num) == 1)
      return $this->replacePlaceholders($singular, $args);
    return $this->replacePlaceholders($message, $args);
  }

  private $list = array();
  public function replaceList($matches) {
    $length = count($this->list);
    $list = '';
    for ($i = 0; $i < $length; $i++) {
      $list .= $this->list[$i];
      if ($i != ($length - 1)) {
        if ($i == ($length - 2))
          $list .= $matches[2];
        else
          $list .= $matches[1];
      }
    }
    return $list;
  }

  public function replacePlaceholders($message, $values = array()) {
    $length = count($values);
    $i = 1;
    foreach ($values as $value) {
      if (is_array($value)) {
        $this->list = $value;
        $message = preg_replace_callback(
          '/%' . $i . '\{(.*?)\}\{(.*?)\}/',
          array($this, 'replaceList'),
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
}

