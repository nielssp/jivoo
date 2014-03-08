<?php
/**
 * Internationalization and localization
 * @package Core
 */
class I18n {
  /** @var AppConfig Configuration */
  private static $config = null;

  /** @var Localization Current localization */
  private static $language = null;
  
  /**
   * Configure I18n system with a configuration
   * @param AppConfig $config Configuration
   * @param string $location Path of language directory
   */
  public static function setup(AppConfig $config, $location) {
    self::$config = $config;
    if (!date_default_timezone_set(self::$config['timeZone'])) {
      date_default_timezone_set('UTC');
    }
    if (isset(self::$config['language'])) {
      $languageFile = $location . '/' . self::$config['language'] . '.lng.php';
      if (file_exists($languageFile)) {
        self::$language = include $languageFile;
        if (!(self::$language instanceof Localization)) {
          self::$language = null;
          throw new I18nException(tr('Language file must return an instance of Localization.'));
        }
        if (isset(self::$config['dateFormat']))
          self::$language->dateFormat = self::$config['dateFormat'];
        if (isset(self::$config['timeFormat']))
          self::$language->timeFormat = self::$config['timeFormat'];
      }
    }
    if (!isset(self::$language))
      self::$language = new Localization();
  }

  /**
   * Translate a string
   * @param string $message Message in english
   * @param mixed $vars,... Values for placeholders starting from %1
   * @return string Translated string
   */
  public static function get($message) {
    if (!isset(self::$language)) {
      self::$language = new Localization();
    }
    $args = func_get_args();
    return call_user_func_array(array(self::$language, 'get'), $args);
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
  public static function getNumeric($message, $singular, $number) {
    if (!isset(self::$language)) {
      self::$language = new Localization();
    }
    $args = func_get_args();
    return call_user_func_array(array(self::$language, 'getNumeric'), $args);
  }

  /**
   * Returns the preferred date format
   *
   * @return string Format string used with date()
   */
  public static function dateFormat() {
    return self::$language->dateFormat;
  }

  /**
   * Returns the preferred time format
   *
   * @return string Format string used with date()
   */
  public static function timeFormat() {
    return self::$language->timeFormat;
  }

  public static function longFormat() {
    return self::$language->longFormat;
  }

  public static function longDate($timestamp = null) {
    return self::date(self::longFormat(), $timestamp);
  }

  public static function shortDate($timestamp = null) {
    $l = self::$language;
    $distance = abs(floor(($timestamp - time()) / (60 * 60 * 24)));
    $cDay = date('d');
    if ($distance >= 60) {
      return self::date($l->monthYear, $timestamp);
    }
    else if ($cDay == date('d', $timestamp)) {
      return tr('Today %1', self::date($l->timeFormat, $timestamp));
    } 
    else if ($cDay == date('d', $timestamp) - 1) {
      return tr('Tomorrow %1', self::date($l->timeFormat, $timestamp));
    } 
    else if ($cDay == date('d', $timestamp) + 1) {
      return tr('Yesterday %1', self::date($l->timeFormat, $timestamp));
    } 
    else if ($distance <= 6 AND $timestamp > time()) {
      return self::date($l->monthDay, $timestamp);
    }
    else {
      return self::date($l->monthYear, $timestamp);
    }
  }

  /**
   * Format date using I18n::dateFormat() 
   * @param int|null $timestamp UNIX timestamp or null for now 
   * @return string Formatted date string
   */
  public static function formatDate($timestamp = null) {
    return self::date(self::dateFormat(), $timestamp);
  }

  /**
   * Format time using I18n::timeFormat() 
   * @param int|null $timestamp UNIX timestamp or null for now 
   * @return string Formatted time string
   */
  public static function formatTime($timestamp = null) {
    return self::date(self::timeFormat(), $timestamp);
  }

  /**
   * Localized date function
   *
   * Works like date() but translates month names and weekday names.
   *
   * @param string $format The format of the outputted date string. See {@link http://dk.php.net/manual/en/function.date.php date()}
   * @param int $timestamp Optional Unix timestamp to use. Default is value of time()
   * @return string Formatted date string
   */
  public static function date($format, $timestamp = null) {
    if (is_null($timestamp))
      $timestamp = time();
    $month = date('n', $timestamp);
    if ($month == 1)
      $F = tr('January');
    else if ($month == 2)
      $F = tr('February');
    else if ($month == 3)
      $F = tr('March');
    else if ($month == 4)
      $F = tr('April');
    else if ($month == 5)
      $F = tr('May');
    else if ($month == 6)
      $F = tr('June');
    else if ($month == 7)
      $F = tr('July');
    else if ($month == 8)
      $F = tr('August');
    else if ($month == 9)
      $F = tr('September');
    else if ($month == 10)
      $F = tr('October');
    else if ($month == 11)
      $F = tr('November');
    else if ($month == 12)
      $F = tr('December ');
    $M = Utilities::substr($F, 0, 3);

    $weekday = date('w', $timestamp);
    if ($weekday == 0)
      $l = tr('Sunday');
    else if ($weekday == 1)
      $l = tr('Monday');
    else if ($weekday == 2)
      $l = tr('Tuesday');
    else if ($weekday == 3)
      $l = tr('Wednesday');
    else if ($weekday == 4)
      $l = tr('Thursday');
    else if ($weekday == 5)
      $l = tr('Friday');
    else if ($weekday == 6)
      $l = tr('Saturday');
    $D = Utilities::substr($l, 0, 3);
    $date = date($format, $timestamp);
    $date = str_replace(date('F', $timestamp), $F, $date);
    $date = str_replace(date('M', $timestamp), $M, $date);
    $date = str_replace(date('l', $timestamp), $l, $date);
    $date = str_replace(date('D', $timestamp), $D, $date);
    return $date;
  }
}

class I18nException extends Exception { }
