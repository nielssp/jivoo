<?php
/*
 * Class for working with HTTP headers, redirects etc.
 *
 * @package PeanutCMS
 */

/**
 * Http class
 */
class Http {

  /**
   * The current path as an array
   * @var array
   */
  private $path;

  /**
   * The current parameters
   * $var array
   */
  private $params;

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    $request = $_SERVER['REQUEST_URI'];
    $request = parse_url($request);
    $this->params = $_GET;
    $path = explode('/', str_replace(WEBPATH, '', urldecode($request['path'])));
    $this->path = array();
    foreach ($path as $dir) {
      if (!empty($dir)) {
        $this->path[] = $dir;
      }
    }
    // Set default settings
    if (!$PEANUT['configuration']->exists('rewrite')) {
      $PEANUT['configuration']->set('rewrite', 'off');
    }
    // Determine if the current URL is correct
    if ($PEANUT['configuration']->get('rewrite') === 'on') {
      if ($this->path[0] == 'index.php') {
        array_shift($this->path);
        $this->redirectPath($this->path, $this->params);
      }
    }
    else {
      if ($this->path[0] != 'index.php') {
        $this->redirectPath($this->path, $this->params);
      }
      array_shift($this->path);
    }
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
   * Redirect the user to another page
   *
   * @todo Might require an absolute URI in some cases? Look into that
   *
   * @param int $status HTTP status code, should be 3xx e.g. 301 for Moved Permanently
   * @param string $location The page to redirect to
   * @return void
   */
  function redirect($status, $location) {
    global $PEANUT;
    if (!$this->setStatus($status)) {
      $PEANUT['errors']->fatal(
        tr('Redirect error'),
        tr('An invalid status code was provided: %1.', '<strong>' . $status . '</strong>')
      );
    }
    header('Location: ' . $location);
    exit();
  }

  /**
   * An internal redirect
   *
   * @param array $path A new path
   * @param array $parameters Additional parameters
   * @param bool $moved If true (default) then a 301 status code will be used,
   * if false then a 303 status code will be used
   * @return void
   */
  function redirectPath($path = null, $parameters = null, $moved = true, $hashtag = null, $rewrite = false) {
    global $PEANUT;
    if (!isset($path)) {
      $path = $this->path;
    }
    if ($moved) {
      $status = 301;
    }
    else {
      $status = 303;
    }
    if (isset($hashtag)) {
      $hashtag = '#' . $hashtag;
    }
    else {
      $hashtag = '';
    }
    if (is_array($parameters) AND count($parameters) > 0) {
      $query = array();
      foreach ($parameters as $key => $value) {
        $query[] = urlencode($key) . '=' . urlencode($value);
      }
      $combined = implode('/', $path) . '?' . implode('&', $query) . $hashtag;
      if ($PEANUT['configuration']->get('rewrite') === 'on' OR $rewrite) {
        $this->redirect($status, WEBPATH . $combined);
      }
      else {
        $this->redirect($status, WEBPATH . 'index.php/' . $combined);
      }
    }
    else {
      if ($PEANUT['configuration']->get('rewrite') === 'on' OR $rewrite) {
        $this->redirect($status, WEBPATH . implode('/', $path) . $hashtag);
      }
      else {
        $this->redirect($status, WEBPATH . 'index.php/' . implode('/', $path) . $hashtag);
      }
    }
  }

  /**
   * Refreshes the current page (e.g. gets rid of post data and reloads the configuration etc.)
   *
   * @param array $parameters Optional alternative parameters-array
   * @return void
   */
  function refreshPath($parameters = null, $hashtag = null) {
    if (!isset($parameters)) {
      $parameters = $this->params;
    }
    $this->redirectPath($this->path, $parameters, false, $hashtag);
  }

  function setStatus($status) {
    $phrase = $this->statusPhrase($status);
    if ($phrase === false)
      return false;
    header('HTTP/1.1 ' . $status . ' ' . $phrase);
    return true;
  }

  /**
   * Returns the phrase for a HTTP status code
   *
   * @param int $status HTTP status code
   * @return string Phrase
   */
  function statusPhrase($status) {
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
   * Create a link to a page
   *
   * @param array $path Path as an array
   * @return string Link
   */
  function getLink($path = null, $parameters = null, $hashtag = null) {
    global $PEANUT;
    if (!isset($path)) {
      $path = $this->path;
    }
    if (isset($hashtag)) {
      $hashtag = '#' . $hashtag;
    }
    else {
      $hashtag = '';
    }
    if (is_array($parameters) AND count($parameters) > 0) {
      $query = array();
      foreach ($parameters as $key => $value) {
        if ($value == '') {
          $query[] = urlencode($key);
        }
        else {
          $query[] = urlencode($key) . '=' . urlencode($value);
        }
      }
      $combined = implode('/', $path) . '?' . implode('&', $query) . $hashtag;
      if ($PEANUT['configuration']->get('rewrite') === 'on') {
        return WEBPATH . $combined;
      }
      else {
        return WEBPATH . 'index.php/' . $combined;
      }
    }
    else {
      if ($PEANUT['configuration']->get('rewrite') === 'on') {
        return WEBPATH . implode('/', $path) . $hashtag;
      }
      else {
        return WEBPATH . 'index.php/' . implode('/', $path) . $hashtag;
      }
    }
  }

  /**
   * Append a query string to a URI.
   *
   * @param string $query Query string e.g. 'p=4&k=3'
   * @param string $uri URI Optional. Default is content of $_SERVER['REQUEST_URI']
   * @return string Uri + query string
   */
  function appendQuery($query, $uri = null) {
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


  /* PROPERTIES BEGIN */

  /**
   * Array of readable property names
   * @var array
   */
  private $_getters = array('params', 'path');
  /**
   * Array of writable property names
   * @var array
   */
  private $_setters = array('params');

  /**
   * Magic getter method
   *
   * @param string $property Property name
   * @throws Exception
   */
  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property)) {
      return call_user_func(array($this, '_get_' . $property));
    }
    else if (in_array($property, $this->_setters)
                OR method_exists($this, '_set_' . $property)) {
      throw new PropertyWriteOnlyException(
        tr('Property "%1" is write-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
  }

  /**
   * Magic setter method
   *
   * @param string $property Property name
   * @param string $value New property value
   * @throws Exception
   */
  public function __set($property, $value) {
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property)) {
      call_user_func(array($this, '_set_' . $property), $value);
    }
    else if (in_array($property, $this->_getters)
                OR method_exists($this, '_get_' . $property)) {
      throw new PropertyReadOnlyException(
        tr('Property "%1" is read-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
  }
  /* PROPERTIES END */
}