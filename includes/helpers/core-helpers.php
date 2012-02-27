<?php
/**
 * PeanutCMS core functions and aliases
 *
 * Functions and aliases to make things easier
 *
 * @package PeanutCMS
 */

/**
 * Translate function alias
 *
 * %n, where n is a number, is used as placeholder for the additional arguments
 *
 * @param string $text Text to translate
 * @param string $args,... Additional OPTIONAL arguments
 * @return string Translated text
 */
function tr($text) {
  global $PEANUT;
  $args = func_get_args();
  if (isset($PEANUT['i18n'])) {
    return call_user_func_array(array($PEANUT['i18n'], 'translate'), $args);
  }
  else {
    $numArgs = func_num_args();
    if ($numArgs > 1) {
      for ($i = 1; $i < $numArgs; $i++) {
        $text = str_replace('%'.$i, $args[$i], $text);
      }
    }
    return $text;
  }
}

/**
 * Translate function to create listings
 *
 * Works somewhat like implode, but more with more options.
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
function trl($single, $plural, $glue, $gluel, $pieces) {
  global $PEANUT;
  $args = func_get_args();
  if (isset($PEANUT['i18n'])) {
    return call_user_func_array(array($PEANUT['i18n'], 'translateList'), $args);
  }
  else {
    if (count($pieces) == 1) {
      $text = $single;
    }
    else {
      $text = $plural;
    }

    $list = '';
    for ($i = 0; $i < count($pieces); $i++) {
      $list .= $pieces[$i];
      if ($i != (count($pieces) - 1)) {
        if ($i == (count($pieces) - 2)) {
          $list .= $gluel;
        }
        else {
          $list .= $glue;
        }
      }
    }
    $text = str_replace('%l', $list, $text);

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 5; $i < $numArgs; $i++) {
        $n = $i-4;
        $text = str_replace('%'.$n, $args[$i], $text);
      }
    }
    return $text;
  }
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
function trn($single, $plural, $number) {
  global $PEANUT;
  $args = func_get_args();
  if (isset($PEANUT['i18n'])) {
    return call_user_func_array(array($PEANUT['i18n'], 'translateNumeral'), $args);
  }
  else {
    if ((int)$number == 1) {
      $text = $single;
    }
    else {
      $text = $plural;
    }

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 2; $i < $numArgs; $i++) {
        $n = $i-1;
        $text = str_replace('%'.$n, $args[$i], $text);
      }
    }
    return $text;
  }
}

/**
 * Comparison function for use with usort() and uasort()
 *
 * @param array $a
 * @param array $b
 */
function prioritySorter($a, $b) {
  if ($a['priority'] < $b['priority']) {
    return 1;
  }
  if ($a['priority'] > $b['priority']) {
    return -1;
  }
  return 0;
}

require_once(PATH . INC . 'helpers/base-object.class.php');

require_once(PATH . INC . 'helpers/selector.class.php');

require_once(PATH . INC . 'helpers/base-model.class.php');


