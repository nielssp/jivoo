<?php
/**
 * Internationalization and localization
 * @package Core
 * @TODO Rethink some of this. E.g. multiple language files, for extensions etc.
 */
class I18n {
  
  /**
   * @var AppConfig Configuration used to pull language, timeZone, dateFormat
   * and timeFormat
   */
  private static $config = null;

  /**
   * @var array Contains the translation strings of the current language
   */
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
      }
    }
  }

  /**
   * Translate function
   *
   * %n where n is a number is used as placeholder for the additional arguments
   *
   * @param string $text Text to translate
   * @param string $args,... Additional OPTIONAL parameters
   * @return string Translated text
   */
  public static function translate($text) {
    if (isset(self::$language) AND !empty(self::$language['tr'][$text]))
      $translated = self::$language['tr'][$text];
    else
      $translated = $text;

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 1; $i < $numArgs; $i++) {
        $translated = str_replace('%' . $i, $args[$i], $translated);
      }
    }
    return $translated;
  }

  /**
   * Translate function to create listings
   *
   * Works somewhat like implode, but with more options.
   * E.g. the sentence "The classes 'errors', 'i18n', and 'configuration' are missing from PeanutCMS" would be translated with:
   *
   * <code>
   * trl("The class '%l' is missing from %1.", "The classes '%l' are missing from %1.", "', '", "', and '", array('errors', 'i18n', 'configuration'), 'PeanutCMS');
   * </code>
   *
   * @param string $single Text to translate if there are only one piece in array
   * @param string $plural Text to translate if there are 0 or more than one pieces in array
   * @param string $glue String to put between pieces in array
   * @param string $gluell String to put between the last two pieces in the array
   * @param array $pieces The array with pieces
   * @param string $args,... Additional OPTIONAL arguments
   * @return string Translated text
   */
  public static function translateList($single, $plural, $glue, $gluel, $pieces) {
    $translate = $single;
    if (!empty(self::$language['trl'][$translate][0]))
      $single = self::$language['trl'][$translate][0];
    if (!empty(self::$language['trl'][$translate][1]))
      $plural = self::$language['trl'][$translate][1];
    if (!empty(self::$language['trl'][$translate][2]))
      $glue = self::$language['trl'][$translate][2];
    if (!empty(self::$language['trl'][$translate][3]))
      $gluel = self::$language['trl'][$translate][3];

    if (count($pieces) == 1)
      $translated = $single;
    else
      $translated = $plural;

    $list = '';
    for ($i = 0; $i < count($pieces); $i++) {
      $list .= $pieces[$i];
      if ($i != (count($pieces) - 1)) {
        if ($i == (count($pieces) - 2))
          $list .= $gluel;
        else
          $list .= $glue;
      }
    }
    $translated = str_replace('%l', $list, $translated);

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 5; $i < $numArgs; $i++) {
        $n = $i - 4;
        $translated = str_replace('%' . $n, $args[$i], $translated);
      }
    }
    return $translated;
  }

  /**
   * Translate function for numbers
   *
   * Used like tr() the placeholder for the number is %1 the optional arguments starts with number %2
   *
   * @param string $single Text to translate if %1 is 1
   * @param string $plural Text to translate if %1 is 0 or greater than 1
   * @param int $number The number to insert with the placeholder %1
   * @param string $args,... Additional OPTIONAL arguments starting with %2
   * @return string Translated text
   */
  public static function translateNumeral($single, $plural, $number) {
    $translate = $single;
    if (!empty(self::$language['trn'][$translate][0]))
      $single = self::$language['trn'][$translate][0];
    if (!empty(self::$language['trn'][$translate][1]))
      $plural = self::$language['trn'][$translate][1];

    if ((int) $number == 1)
      $translated = $single;
    else
      $translated = $plural;

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 2; $i < $numArgs; $i++) {
        $n = $i - 1;
        $translated = str_replace('%' . $n, $args[$i], $translated);
      }
    }
    return $translated;
  }

  /**
   * Returns the preferred date format
   *
   * @return string Format string used with date()
   */
  public static function dateFormat() {
    if (isset(self::$config['dateFormat']))
      return self::$config['dateFormat'];
    else if (!empty(self::$language['defaultDateFormat']))
      return self::$language['defaultDateFormat'];
    else
      return 'Y-m-d';
  }

  /**
   * Returns the preferred time format
   *
   * @return string Format string used with date()
   */
  public static function timeFormat() {
    if (isset(self::$config['timeFormat']))
      return self::$config['timeFormat'];
    else if (!empty(self::$language['defaultTimeFormat']))
      return self::$language['defaultTimeFormat'];
    else
      return 'H:i:s';
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
