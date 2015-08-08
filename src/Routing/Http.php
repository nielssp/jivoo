<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Provides functions related to redirects and HTTP status codes.
 */
class Http {

  const OK = 200;
  const CREATED = 201;
  const ACCEPTED = 202;
  const NO_CONTENT = 204;
  const MULTIPLE_CHOICES = 300;
  const MOVED_PERMANENTLY = 301;
  const FOUND = 302;
  const SEE_OTHER = 303;
  const NOT_MODIFIED = 304;
  const USE_PROXY = 305;
  const SWITCH_PROXY = 306;
  const TEMPORARY_REDIRECT = 307;
  const BAD_REQUEST = 400;
  const UNAUTHORIZED = 401;
  const PAYMENT_REQUIRED = 402;
  const FORBIDDEN = 403;
  const NOT_FOUND = 404;
  const METHOD_NOT_ALLOWED = 405;
  const NOT_ACCEPTABLE = 406;
  const INTERNAL_SERVER_ERROR = 500;
  const NOT_IMPLEMENTED = 501;
  const SERVICE_UNAVAILABLE = 503;

  private function __construct() { }
  
  /**
   * Redirect the user to another page.
   *
   * @param int $status HTTP status code, should be 3xx e.g. 301 for Moved Permanently.
   * @param string $location The page to redirect to.
   * @throws InvalidStatusException IF status code is undefined.
   */
  public static function redirect($status, $location) {
    if (!Http::setStatus($status)) {
      throw new InvalidStatusException(
        tr('An invalid status code was provided: %1.', '<strong>' . $status . '</strong>')
      );
    }
    Http::assumeHeadersNotSent();
    header('Location: ' . $location);
    exit();
  }
  
  /**
   * Set HTTP content type (and encoding)
   * @param string $type Content type, e.g. 'text/html'.
   * @param string $encoding Content encoding (charset), e.g. 'UTF-8'.
   */
  public static function setContentType($type, $encoding = 'UTF-8') {
    header('Content-Type: ' . $type . '; charset=' . $encoding);
  }
  
  /**
   * Set HTTP status code.
   * @param int $status Status code.
   * @return boolean False if invalid/unknown/unimplemented code, true otherwise.
   */
  public static function setStatus($status) {
    $phrase = Http::statusPhrase($status);
    if ($phrase === false) {
      return false;
    }
    header('HTTP/1.1 ' . $status . ' ' . $phrase);
    return true;
  }
  
  /**
   * Returns the phrase for a HTTP status code.
   * @param int $status HTTP status code.
   * @return string Phrase.
   */
  public static function statusPhrase($status) {
    switch ($status) {
      case 200:
        return 'OK';
      case 201:
        return 'Created';
      case 202:
        return 'Accepted';
      case 204:
        return 'No Content';
      case 300:
        return 'Multiple Choices';
      case 301:
        return 'Moved Permanently';
      case 302:
        return 'Found';
      case 303:
        return 'See Other';
      case 304:
        return 'Not Modified';
      case 305:
        return 'Use Proxy';
      case 306:
        return 'Switch Proxy';
      case 307:
        return 'Temporary Redirect';
      case 400:
        return 'Bad Request';
      case 401:
        return 'Unauthorized';
      case 402:
        return 'Payment Required';
      case 403:
        return 'Forbidden';
      case 404:
        return 'Not Found';
      case 405:
        return 'Method Not Allowed';
      case 406:
        return 'Not Acceptable';
      case 500:
        return 'Internal Server Error';
      case 501:
        return 'Not Implemented';
      case 503:
        return 'Service Unavailable';
    }
    return false;
  }
  
  /**
   * Encode a query.
   * @param string[] $query Query array.
   * @param bool $associative If set to false the input
   * <code>array('value1', 'value2', 'value3')</code> will result in the output
   * string "value1&value2&value3", and any keys will be ignored. If set to true
   * (the default) the above array will result in the output
   * "0=value1&1=value2&2=value3" to match the format of PHP's global
   * {@see $_GET}-array. 
   * @return string Query string without leading '?'. 
   */
  public static function encodeQuery(array $query, $associative = true) {
    $queryString = array();
    foreach ($query as $key => $value) {
      if ($associative) {
        if ($key === '')
          continue;
        if ($value === '')
          $queryString[] = urlencode($key);
        else
          $queryString[] = urlencode($key) . '=' . urlencode($value);
      }
      else {
        if ($value === '')
          continue;
        $queryString[] = urlencode($value);
      }
    }
    return implode('&', $queryString);
  }
  
  /**
   * Decode a query string.
   * @param string $query Query string with or without leading '?'.
   * @param bool $associative If set to false the function expects the query
   * string to be of the form "value1&value2&value3" resulting in the output
   * <code>array('value1', 'value2', 'value3')</code> (any keys will be
   * ignored). If set to true (the default) the above string will result in the
   * output: <code>array('value1' => '', 'value2' => '', 'value3' => '')</code>
   * to match the format of PHP's global {@see $_GET}-array. 
   * @return string[] Query array.
   */
  public static function decodeQuery($query, $associative = true) {
    if ($query == '' or $query == '?')
      return array();
    if ($query[0] == '?')
      $query = substr($query, 1);
    $queryString = explode('&', $query);
    $query = array();
    foreach ($queryString as $string) {
      if (strpos($string, '=') !== false) {
        list($key, $value) = explode('=', $string, 2);
        if ($key === '')
          continue;
        if ($associative)
          $query[urldecode($key)] = urldecode($value);
        else
          $query[] = urldecode($value);
      }
      else if ($string === '') {
        continue;
      }
      else if ($associative) {
        $query[urldecode($string)] = '';
      }
      else {
        $query[] = urldecode($string);
      }
    }
    return $query;
  }

  /**
   * Format a date for use in HTTP headers.
   * @param int $timestamp Time stamp.
   * @return string Formatted date.
   */
  public static function date($timestamp) {
    return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
  }
  
  /**
   * Throw an exception if headers have already been sent and can't be changed.
   * @throws HeadersSentException If headers already sent.
   */
  public static function assumeHeadersNotSent() {
    if (headers_sent($file, $line)) {
      throw new HeadersSentException(tr(
        'Headers already sent in file %1 on line %2', $file, $line
      ));
    }
  }
}
