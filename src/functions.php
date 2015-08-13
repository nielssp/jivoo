<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.

/**
 * Translate a string. Alias for {@see \Jivoo\Core\I18n\I18n:get}.
 * @param string $message Message in english.
 * @param mixed $vars,... Values for placeholders starting from %1.
 * @return string Translated string.
 */
function tr($message) {
  $args = func_get_args();
  return call_user_func_array(array('Jivoo\Core\I18n\I18n', 'get'), $args);
}

/**
 * Translate a string containing a numeric value.
 * Alias for {@see \Jivoo\Core\I18n\I18n:nget}.
 *
 * For instance:
 * <code>
 * $l->nget('This post has %1 comments', 'This post has %1 comment', $numcomments);
 * </code>
 *
 * @param string $plural Message in english (plural).
 * @param string $singular Singular version of message in english.
 * @param mixed $vars,... Values for placholders starting from %1, the first one (%1) is the
 * numeral to test.
 * @return Translated string.
 */
function tn($plural, $singular, $number) {
  $args = func_get_args();
  return call_user_func_array(array('Jivoo\Core\I18n\I18n', 'nget'), $args);
}

/**
 * Encode string for HTML usage.
 * @param string $string Input.
 * @return string Output.
 */
function h($string) {
  return htmlentities(strval($string), ENT_COMPAT, 'UTF-8');
}

/**
 * Alias for {@see \Jivoo\Models\Condition\Condition} constructor.
 * @param \Jivoo\Models\ConditionICondition|string Condition.
 * @return \Jivoo\Models\Condition\Condition Condition object.
 */
function where($condition) {
  $args = func_get_args();
  $condition = new Jivoo\Models\Condition\Condition();
  call_user_func_array(array($condition, 'andWhere'), $args);
  return $condition;
}

/**
 * Format time using preferred locale format. 
 * @param int|null $timestamp UNIX timestamp or null for current timestamp.
 * @param string $style Which style to use ('short', 'medium', or 'long').
 * @return string Formatted time.
 * @see \Jivoo\Core\I18n\I18n::formatDate()
 */
function fdate($timestamp = null, $style = 'short') {
  return Jivoo\Core\I18n\I18n::formatDate($timestamp);
}

/**
 * Format date using preferred locale format.
 * @param int|null $timestamp UNIX timestamp or null for current timestamp.
 * @param string $style Which style to use ('short', 'medium', or 'long').
 * @return string Formatted date.
 * @see \Jivoo\Core\I18n\I18n::formatTime()
 */
function ftime($timestamp = null, $style = 'short') {
  return Jivoo\Core\I18n\I18n::formatTime($timestamp);
}

/**
 * Format date and time using preferred locale format.
 * @param int|null $timestamp UNIX timestamp or null for current timestamp.
 * @param string $style Which style to use ('short', 'medium', or 'long').
 * @return string Formatted date and time.
 * @see \Jivoo\Core\I18n\I18n::formatDateTime()
 */
function fdatetime($timestamp = null, $style = 'short') {
  return Jivoo\Core\I18n\I18n::formatDateTime($timestamp);
}

/**
 * @see I18n::longDate()
 * @deprecated
 */
function ldate($timestamp = null) {
  return Jivoo\Core\I18n\I18n::longDate($timestamp);
}

/**
 * Localized date function.
 * @see \Jivoo\Core\I18n\I18n::shortDate()
 * @deprecated
 */
function sdate($timestamp = null) {
  return Jivoo\Core\I18n\I18n::shortDate($timestamp);
}

/**
 * Localized date formatting function.
 * @see \Jivoo\Core\I18n\I18n::date()
 * @param string $format The format of the outputted date string. See
 * {@link http://php.net/manual/en/function.date.php date()}
 * @param int $timestamp Optional Unix timestamp to use. Default is value of 
 * {@see time()}
 * @return string Formatted date string.
 */
function tdate($format, $timestamp = null) {
  return Jivoo\Core\I18n\I18n::date($format, $timestamp);
}

/**
 * Precondition function that can be used to add additional constraints to
 * function parameters.
 * @param bool $condition Condition.
 * @param string $message Failure message.
 * @throws \Jivoo\InvalidArgumentException If condition is false.
 */
function assume($condition, $message = null) {
  if ($condition === true) {
    return;
  }
  if (isset($message))
    throw new Jivoo\InvalidArgumentException($message);
  $bt = debug_backtrace();
  $call = $bt[0];
  $lines = file($call['file']);
  preg_match(
    '/' . $call['function'] . '\((.+)\)/',
    $lines[$call['line'] - 1],
    $matches
  );
  throw new Jivoo\InvalidArgumentException(tr('Assumption failed: %1', $matches[1]));
}