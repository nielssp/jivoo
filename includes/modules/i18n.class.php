<?php
/*
 * Internationalization and localization of PeanutCMS
 *
 * @package PeanutCMS
 */

/**
 * Internationalization and localization class
 */
class I18n {

  /**
   * Contains the translation strings of the current language
   * @var array
   */
  var $language;

  /**
   * Contain the language code of current language, e.g. "da"
   * @var string
   */
  var $languageCode;

  /**
   * Constructor
   */
  function I18n() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    $this->language = array();
    if (defined('DATETIMEZONE_AVAILABLE')) {
      if (!date_default_timezone_get())
        date_default_timezone_set('UTC');
    }
    // Get default language
    $this->getLanguage();
    if (isset($PEANUT['configuration']))
      $this->configure();
    else
      $PEANUT['hooks']->attach('configurationReady', array($this, 'configure'));
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  /**
   * Configure i18n
   *
   * @return void
   */
  function configure() {
    global $PEANUT;
    // Set default language
    if (!$PEANUT['configuration']->exists('language'))
      $PEANUT['configuration']->set('language', LANGUAGE);
    // Get language from configuration instead
    $this->getLanguage();

    // Set default settings
    if (!$PEANUT['configuration']->exists('dateFormat'))
      $PEANUT['configuration']->set('dateFormat', $this->dateFormat());
    if (!$PEANUT['configuration']->exists('timeFormat'))
      $PEANUT['configuration']->set('timeFormat', $this->timeFormat());
    if (!$PEANUT['configuration']->exists('timeFormat'))
      $PEANUT['configuration']->set('timeFormat', $this->timeFormat());
    if (!$PEANUT['configuration']->exists('timeZone')) {
      if (defined('DATETIMEZONE_AVAILABLE'))
        $PEANUT['configuration']->set('timeZone', date_default_timezone_get());
      else
        $PEANUT['configuration']->set('timeZone', TIMEZONE_OFFSET);
    }

    // Set time zone
    if (defined('DATETIMEZONE_AVAILABLE')) {
      if (!date_default_timezone_set($PEANUT['configuration']->get('timeZone')))
        date_default_timezone_set('UTC');
    }
    else {
      if (!defined('LOCAL_TIMEZONE_OFFSET')) {
        $timeZoneOffset = $PEANUT['configuration']->get('timeZone');
        if (is_int($timeZoneOffset))
          define('LOCAL_TIMEZONE_OFFSET', $timeZoneOffset);
        else
          define('LOCAL_TIMEZONE_OFFSET', 0);
      }
    }
  }

  /**
   *
   *
   * @global <type> $PEANUT
   * @return void
   */
  function getLanguage() {
    global $PEANUT;
    if (isset($PEANUT['configuration']) AND $language = $PEANUT['configuration']->get('language')) {
      if (file_exists(PATH . LANG . $language . '.lng.php')) {
        include(PATH . LANG . $language . '.lng.php');
        $this->language = $translate[$language];
        $this->languageCode = $language;
        return;
      }
      else {
        $PEANUT['errors']->notification('warning', tr('The language, "%1", is missing', $language), true, 'language-missing');
      }
    }
    if (!defined('LANGUAGE')) {
      $PEANUT['errors']->notification('warning', tr('No default language has been defined'), true, 'default-language');
      return;
    }
    if(!file_exists(PATH . LANG . LANGUAGE . '.lng.php')) {
      $PEANUT['errors']->notification('warning', tr('The default language, "%1", is missing', LANGUAGE), true, 'default-language-missing');
      return;
    }
    include(PATH . LANG . LANGUAGE . '.lng.php');
    $this->language = $translate[LANGUAGE];
    $this->languageCode = LANGUAGE;
  }

  /**
   * Translate function
   *
   * %n where n is a number is used as placeholder for the additional arguments
   *
   * @param string $text Text to translate
   * @param string $args,... Additional OPTIONAL arguments
   * @return string Translated text
   */
  function translate($text) {
    if (!empty($this->language['tr'][$text]))
      $translated = $this->language['tr'][$text];
    else
      $translated = $text;

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 1; $i < $numArgs; $i++) {
        $translated = str_replace('%'.$i, $args[$i], $translated);
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
  function translateList($single, $plural, $glue, $gluel, $pieces) {
    $translate = $single;
    if (!empty($this->language['trl'][$translate][0]))
      $single = $this->language['trl'][$translate][0];
    if (!empty($this->language['trl'][$translate][1]))
      $plural = $this->language['trl'][$translate][1];
    if (!empty($this->language['trl'][$translate][2]))
      $glue = $this->language['trl'][$translate][2];
    if (!empty($this->language['trl'][$translate][3]))
      $gluel = $this->language['trl'][$translate][3];

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
        $n = $i-4;
        $translated = str_replace('%'.$n, $args[$i], $translated);
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
  function translateNumeral($single, $plural, $number) {
    $translate = $single;
    if (!empty($this->language['trn'][$translate][0]))
      $single = $this->language['trn'][$translate][0];
    if (!empty($this->language['trn'][$translate][1]))
      $plural = $this->language['trn'][$translate][1];
    
    if ((int)$number == 1)
      $translated = $single;
    else
      $translated = $plural;

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 2; $i < $numArgs; $i++) {
        $n = $i-1;
        $translated = str_replace('%'.$n, $args[$i], $translated);
      }
    }
    return $translated;
  }

  /**
   * Returns the preferred date format
   *
   * @return string Format string used with date()
   */
  function dateFormat() {
    global $PEANUT;
    if (isset($PEAUT['configuration']) AND $dateFormat = $PEANUT['configuration']->get('dateFormat'))
      return $dateFormat;
    else if (!empty($this->language['defaultDateFormat']))
      return $this->language['defaultDateFormat'];
    else
      return 'Y-m-d';
  }

  /**
   * Returns the preferred time format
   *
   * @return string Format string used with date()
   */
  function timeFormat() {
    global $PEANUT;
    if (isset($PEAUT['configuration']) AND $timeFormat = $PEANUT['configuration']->get('timeFormat'))
      return $timeFormat;
    else if (!empty($this->language['defaultTimeFormat']))
      return $this->language['defaultTimeFormat'];
    else
      return 'H:i:s';
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
  function date($format, $timestamp = null) {
    if (is_null($timestamp))
      $timestamp = time();
    if (!defined('DATETIMEZONE_AVAILABLE') AND defined('LOCAL_TIMEZONE_OFFSET')) {
      $timestamp = $timestamp - TIMEZONE_OFFSET + LOCAL_TIMEZONE_OFFSET;
    }
    $month = date('n', $timestamp);
    if ($month == 1) $F = tr('January');
    elseif ($month == 2) $F = tr('February');
    elseif ($month == 3) $F = tr('March');
    elseif ($month == 4) $F = tr('April');
    elseif ($month == 5) $F = tr('May');
    elseif ($month == 6) $F = tr('June');
    elseif ($month == 7) $F = tr('July');
    elseif ($month == 8) $F = tr('August');
    elseif ($month == 9) $F = tr('September');
    elseif ($month == 10) $F = tr('October');
    elseif ($month == 11) $F = tr('November');
    elseif ($month == 12) $F = tr('December ');
    $M = utf8_encode(substr(utf8_decode($F), 0, 3));

    $weekday = date('w', $timestamp);
    if ($weekday == 0) $l = tr('Sunday');
    elseif ($weekday == 1) $l = tr('Monday');
    elseif ($weekday == 2) $l = tr('Tuesday');
    elseif ($weekday == 3) $l = tr('Wednesday');
    elseif ($weekday == 4) $l = tr('Thursday');
    elseif ($weekday == 5) $l = tr('Friday');
    elseif ($weekday == 6) $l = tr('Saturday');
    $D = utf8_encode(substr(utf8_decode($l), 0, 3));
    $date = date($format, $timestamp);
    $date = str_replace(date('F', $timestamp), $F, $date);
    $date = str_replace(date('M', $timestamp), $M, $date);
    $date = str_replace(date('l', $timestamp), $l, $date);
    $date = str_replace(date('D', $timestamp), $D, $date);
    return $date;
  }

  /**
   * Return an array of languages installed in the language-directory
   * 
   * @param bool $full If true then returns all info about a language
   * @return array|false Sorted array where languagecode => language or false on error
   */
  function listLanguages($full = false) {
    $dir = opendir(PATH . LANG);
    if ($dir) {
      $return = array();
      $simple = array();
      $languages = array();
      while (($language = readdir($dir)) !== false) {
        if (is_file(PATH . LANG . $language) AND $language != '.' AND $language != '..') {
          $languageext = explode('.', $language);
          if ($languageext[2] == 'php' AND $languageext[1] == 'lng') {
            $languages[] = $languageext[0];
          }
        }
      }
      closedir($dir);
      asort($languages);
      foreach($languages as $language) {
        $langdesc = file_get_contents(PATH . LANG . $language . '.lng.php');
        $langdesc = explode('/*Language', $langdesc);
        $langdesc = explode('*/', $langdesc[1]);
        $langdesc = explode("\n", $langdesc[0]);
        $return[$language] = array();
        foreach($langdesc as $line) {
          $line = explode(':', $line, 2);
          if (is_array($line) AND isset($line[0]) AND isset($line[1])) {
            list($key, $value) = $line;
            $key = strtolower(trim($key));
            $value = trim($value);
            if ($key != '' AND $value != '') {
              if (isset($return[$language][$key])) {
                $old = $return[$language][$key];
                if (!is_array($return[$language][$key])) {
                  $return[$language][$key] = array();
                  $return[$language][$key][] = $old;
                }
                $return[$language][$key][] = $value;
              }
              else {
                $return[$language][$key] = $value;
              }
            }
          }
        }
        $simple[$language] = $return[$language]['name'] . '/' . $return[$language]['local name'];
      }
      asort($simple);
      if ($full)
        return $return;
      else
        return $simple;
    }
    else {
      return false;
    }
  }

}