<?php
/**
 * Provides functions related to redirects and HTTP status codes
 * @package Core
 * @subpackage Routing
 */
class Http {
  private function __construct() { }
  
  /**
   * Redirect the user to another page
   *
   * @todo Might require an absolute URI in some cases? Look into that
   *
   * @param int $status HTTP status code, should be 3xx e.g. 301 for Moved Permanently
   * @param string $location The page to redirect to
   * @return void
   */
  public static function redirect($status, $location) {
    if (!Http::setStatus($status)) {
      throw new Exception(
        tr('An invalid status code was provided: %1.', '<strong>' . $status . '</strong>')
      );
    }
    header('Location: ' . $location);
    exit();
  }
  
  /**
   * Set HTTP status code
   * @param int $status Status code
   * @return boolean False if invalid/unknown/unimplemented code, true otherwise
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
   * Returns the phrase for a HTTP status code
   * @param int $status HTTP status code
   * @return string Phrase
   */
  public static function statusPhrase($status) {
    switch ($status) {
      case 200:
        return 'OK';
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
      case 404:
        return 'Not Found';
    }
    return false;
  }
  
  /**
   * Append a query string to a URI.
   * @param string $query Query string e.g. 'p=4&k=3'
   * @param string $uri URI Optional. Default is content of $_SERVER['REQUEST_URI']
   * @deprecated Not used anywhere... Use routing system instead
   * @return string Uri + query string
   */
  public static function appendQuery($query, $uri = null) {
    // Just in case someone puts a question mark at the beginning
    $query = ltrim($query, '?');
    if (is_null($uri)) {
      $uri = $_SERVER['REQUEST_URI'];
    }
    if (strpos($uri, '?') !== false) {
      return $uri . '&' . $query;
    }
    else {
      return $uri . '?' . $query;
    }
  }
}