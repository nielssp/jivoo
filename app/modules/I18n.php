<?php
// Module
// Name           : I18n
// Version        : 0.2.0
// Description    : The PeanutCMS internationalization and localization system
// Author         : PeanutCMS
// Dependencies   : Errors Configuration

/*
 * Internationalization and localization of PeanutCMS
 *
 * @package PeanutCMS
 */

/**
 * Internationalization and localization class
 */
class I18n extends ModuleBase implements ITranslationService {

  /**
   * Contains the translation strings of the current language
   * @var array
   */
  private $language;

  /**
   * Contains the language code of current language, e.g. "da"
   * @var string
   */
  private $languageCode;

  protected function init() {
    TranslationService::setService($this);

    $this->language = array();
    if (!date_default_timezone_get())
      date_default_timezone_set('UTC');
    // Get default language
    $this->configure();
  }

  /**
   * Configure i18n
   *
   * @return void
   */
  private function configure() {
    // Set default language
    $this->m->Configuration->setDefault('i18n.language', LANGUAGE);
    // Get language from configuration instead
    $this->getLanguage();

    // Set default settings
    $this->m->Configuration->setDefault(array(
      'i18n.dateFormat' => $this->dateFormat(),
      'i18n.timeFormat' => $this->timeFormat(),
      'i18n.timeZone' => date_default_timezone_get()
    ));

    // Set time zone
    if (!date_default_timezone_set($this->m->Configuration->get('i18n.timeZone')))
      date_default_timezone_set('UTC');
  }

  /**
   *
   *
   * @return void
   */
  private function getLanguage() {
    if ($language = $this->m->Configuration->get('language')) {
      if (file_exists(p(LANG . $language . '.lng.php'))) {
        include(p(LANG . $language . '.lng.php'));
        $this->language = $translate[$language];
        $this->languageCode = $language;
        return;
      }
      else {
        new GlobalWarning(
          tr('The language "%1", is missing', $language),
          'language-missing'
        );
      }
    }
    if (!defined('LANGUAGE')) {
      new GlobalWarning(
        tr('No default language has been defined'), 'default-language'
      );
      return;
    }
    if(!file_exists(PATH . LANG . LANGUAGE . '.lng.php')) {
      new GlobalWarning(
        tr('The default language, "%1", is missing', LANGUAGE),
        'default-language-missing'
      );
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
  public function translate($text) {
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
  public function translateList($single, $plural, $glue, $gluel, $pieces) {
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
  public function translateNumeral($single, $plural, $number) {
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
  public function dateFormat() {
    if ($dateFormat = $this->m->Configuration->get('i18n.dateFormat'))
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
  public function timeFormat() {
    if ($timeFormat = $this->m->Configuration->get('i18n.timeFormat'))
      return $timeFormat;
    else if (!empty($this->language['defaultTimeFormat']))
      return $this->language['defaultTimeFormat'];
    else
      return 'H:i:s';
  }

  public function fdate($timestamp = NULL) {
    return $this->date($this->dateFormat(), $timestamp);
  }

  public function ftime($timestamp = NULL) {
    return $this->date($this->timeFormat(), $timestamp);
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
  public function date($format, $timestamp = NULL) {
    if (is_null($timestamp))
      $timestamp = time();
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
   * @TODO Forgot about this? :S Fix this pile of junk
   * @param bool $full If true then returns all info about a language
   * @return array|false Sorted array where languagecode => language or false on error
   */
  public function listLanguages($full = false) {
    $dir = opendir(p(LANG));
    if ($dir) {
      $return = array();
      $simple = array();
      $languages = array();
      while (($language = readdir($dir)) !== false) {
        if (is_file(p(LANG . $language)) AND $language != '.' AND $language != '..') {
          $languageext = explode('.', $language);
          if ($languageext[2] == 'php' AND $languageext[1] == 'lng') {
            $languages[] = $languageext[0];
          }
        }
      }
      closedir($dir);
      asort($languages);
      foreach($languages as $language) {
        $langdesc = file_get_contents(p(LANG . $language . '.lng.php'));
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
