<?php
class Localization {
  private $messages = array();

  private $dateFormat = 'Y-m-d';
  private $timeFormat = 'H:i';

  private $longFormat = '%DATE %TIME';
  private $monthYear = 'F Y';
  private $monthDay = 'F j';
  private $weekDay = 'l %TIME';

  public function __construct() { }

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
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'dateFormat':
      case 'timeFormat':
      case 'longFormat':
      case 'monthYear':
      case 'monthDay':
      case 'weekDay':
        $this->$property = $value;
    }
  }

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
  }
  /**
   * Extend this localization with additional messages from another one
   * @param Localization $l Other localization object
   */
  public function extend(Localization $l) {
    $this->messages = array_merge($this->messages, $l->messages);
  }

  /**
   * Set translation string
   * @param string $message Message in english
   * @param string $translation Translation string
   * @param string $patterns,... Regular expression patterns to match message variables
   * against
   */
  public function set($message, $translation) {
    $args = func_get_args();
    array_shift($args);
    if (!isset($this->messages[$message]))
      $this->messages[$message] = array();
    $this->messages[$message][] = $args;
  }

  /**
   * Translate a string
   * @param string $message Message in english
   * @param mixed $vars,... Values for placeholders starting from %1
   * @return string Translated string
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
   * @param string $message Message in english (plural)
   * @param string $singular Singular version of message in english
   * @param mixed $vars,... Values for placholders starting from %1, the first one (%1) is the
   * numeral to test
   * @return Translated string
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

