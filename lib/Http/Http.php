<?php
// Module
// Name           : HTTP
// Version        : 0.3.0
// Description    : The PeanutCMS http system
// Author         : PeanutCMS
// Dependencies   : Errors Configuration

/*
 * Class for working with HTTP headers, redirects etc.
 *
 * @package PeanutCMS
 */

/**
 * Http class
 */
class Http extends ModuleBase {

  public function getRequest() {
    return $this->request;
  }

  protected function init() {
    // Set default settings
    $this->m->Configuration->setDefault(array(
      'http.rewrite' => 'off',
      'http.index.path' => 'posts'
    ));

    $this->request = new Request();

    // Determine if the current URL is correct
    if ($this->m->Configuration->get('http.rewrite') == 'on') {
      if (isset($this->request->path[0]) AND $this->request->path[0] == 'index.php') {
        array_shift($this->request->path);
        $this->redirectPath($this->request->path, $this->request->query);
      }
    }
    else {
      if (!isset($this->request->path[0]) OR $this->request->path[0] != 'index.php') {
        $this->redirectPath($this->request->path, $this->request->query);
      }
      $path = $this->request->path;
      array_shift($path);
      $this->request->path = $path;
    }

    $path = explode('/', $this->m->Configuration->get('http.index.path'));
    $query = $this->m->Configuration->get('http.index.query', true);
    if (count($this->request->path) < 1) {
      $this->request->path = $path;
      $this->request->query = array_merge($query, $this->request->query);
    }
    else if ($path == $this->request->path) {
      $this->redirectPath(array(), $this->request->query);
    }
    
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
    if (!Http::setStatus($status)) {
      /** @todo Should be an exception... */
      Errors::fatal(
        tr('Redirect error'),
        tr('An invalid status code was provided: %1.', '<strong>' . $status . '</strong>')
      );
    }
    if (defined('ALLOW_REDIRECT') AND ALLOW_REDIRECT) {
      header('Location: ' . $location);
      exit();
    }
    else {
      throw new RedirectNotAllowedException(tr('Redirect not allowed'));
    }
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
  public function redirectPath($path = null, $query = null, $moved = true, $fragment = null, $rewrite = false) {
    $status = $moved ? 301 : 303;
    $this->redirect($status, $this->getLink($path, $query, $fragment));
  }

  /**
   * Refreshes the current page (e.g. gets rid of post data and reloads the configuration etc.)
   *
   * @param array $parameters Optional alternative parameters-array
   * @return void
   */
  public function refreshPath($query = null, $fragment = null) {
    if (!isset($query)) {
      $query = $this->request->query;
    }
    $this->redirectPath($this->request->path, $query, false, $fragment);
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
  public function getLink($path = null, $query = null, $fragment = null, $rewrite = false) {
    if (!isset($path)) {
      $path = $this->request->path;
    }
    $index = explode('/', $this->m->Configuration->get('http.index.path'));
    if ($index == $path) {
      $path = array();
    }
    if (isset($fragment)) {
      $fragment = '#' . $fragment;
    }
    else {
      $fragment = '';
    }
    if (is_array($query) AND count($query) > 0) {
      $queryStrings = array();
      foreach ($query as $key => $value) {
        if ($value == '') {
          $queryStrings[] = urlencode($key);
        }
        else {
          $queryStrings[] = urlencode($key) . '=' . urlencode($value);
        }
      }
      $combined = implode('/', $path) . '?' . implode('&', $queryStrings) . $fragment;
      if ($this->m->Configuration->get('http.rewrite') === 'on' OR $rewrite) {
        return $this->w($combined);
      }
      else {
        return $this->w('index.php/' . $combined);
      }
    }
    else {
      if ($this->m->Configuration->get('http.rewrite') === 'on' OR $rewrite) {
        return $this->w(implode('/', $path) . $fragment);
      }
      else {
        return $this->w('index.php/' . implode('/', $path) . $fragment);
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

class RedirectNotAllowedException extends Exception { }
