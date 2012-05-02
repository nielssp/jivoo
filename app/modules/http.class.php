<?php
/*
 * Class for working with HTTP headers, redirects etc.
 *
 * @package PeanutCMS
 */

/**
 * Http class
 */
class Http implements IModule {

  private $errors;

  private $configuration;

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getErrors() {
    return $this->errors;
  }

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
  public function __construct(Configuration $configuration) {
    global $PEANUT;
    $this->configuration = $configuration;
    $this->errors = $this->configuration->getErrors();

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
    if (!$this->configuration->exists('http.rewrite')) {
      $this->configuration->set('http.rewrite', 'off');
    }

    if (!$this->configuration->exists('http.index')) {
      $this->configuration->set(
          	'http.index',
            array(
              'path' => array('posts'),
              'parameters' => array()
            )
      );
    }
    // Determine if the current URL is correct
    if ($this->configuration->get('rewrite') === 'on') {
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

    $index = $this->configuration->get('http.index');
    if (count($this->path) < 1) {
      $this->path = $index['path'];
      $this->params = array_merge($index['parameters'], $this->params);
    }
    else if ($index['path'] == $this->path) {
      $this->redirectPath(array(), $this->params);
    }
  }

  public static function getDependencies() {
    return array('configuration');
  }


  public function getPath() {
    return $this->path;
  }

  public function getParams() {
    return $this->params;
  }

  public function unsetParam($key) {
    unset($this->params[$key]);
  }

  public function isCurrent($path) {
    return $path === $this->path;
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
  public function redirect($status, $location) {
    global $PEANUT;
    if (!Http::setStatus($status)) {
      $this->errors->fatal(
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
  public function redirectPath($path = null, $parameters = null, $moved = true, $hashtag = null, $rewrite = false) {
    global $PEANUT;
    if (!isset($path)) {
      $path = $this->path;
    }
    $index = $this->configuration->get('http.index');
    if ($index['path'] == $path) {
      $path = array();
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
      if ($this->configuration->get('http.rewrite') === 'on' OR $rewrite) {
        $this->redirect($status, w($combined));
      }
      else {
        $this->redirect($status, w('index.php/' . $combined));
      }
    }
    else {
      if ($this->configuration->get('http.rewrite') === 'on' OR $rewrite) {
        $this->redirect($status, w(implode('/', $path) . $hashtag));
      }
      else {
        $this->redirect($status, w('index.php/' . implode('/', $path) . $hashtag));
      }
    }
  }

  /**
   * Refreshes the current page (e.g. gets rid of post data and reloads the configuration etc.)
   *
   * @param array $parameters Optional alternative parameters-array
   * @return void
   */
  public function refreshPath($parameters = null, $hashtag = null) {
    if (!isset($parameters)) {
      $parameters = $this->params;
    }
    $this->redirectPath($this->path, $parameters, false, $hashtag);
  }

  public static function setStatus($status) {
    $phrase = Http::statusPhrase($status);
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
   * Create a link to a page
   *
   * @param array $path Path as an array
   * @return string Link
   */
  public function getLink($path = null, $parameters = null, $hashtag = null) {
    global $PEANUT;
    if (!isset($path)) {
      $path = $this->path;
    }
    $index = $this->configuration->get('http.index');
    if ($index['path'] == $path) {
      $path = array();
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
      if ($this->configuration->get('http.rewrite') === 'on') {
        return w($combined);
      }
      else {
        return w('index.php/' . $combined);
      }
    }
    else {
      if ($this->configuration->get('http.rewrite') === 'on') {
        return w(implode('/', $path) . $hashtag);
      }
      else {
        return w('index.php/' . implode('/', $path) . $hashtag);
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
