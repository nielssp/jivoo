<?php
/**
 * A class representing a HTTP request
 * @package PeanutCMS
 * @property array $path The path relative to the application root as an array
 * @property array $query The GET query as an array
 * @property string $fragment The fragment
 * @property-read array $realPath The original $path
 * @property-read array $data POST data as an array
 * @property-read Cookies $cookies Cookie access object
 * @property-read Session $session Session storage access object
 * @property-read string|null $ip The remote address or null if not set
 * @property-read string|null $url The request uri or null if not set
 * @property-read string|null $referer HTTP referer or null if not set
 */
class Request {

  private $realPath;

  private $path;

  private $query;

  private $cookies;

  private $session;

  private $fragment = null;

  private $data;

  /**
   * Initializes the request-object
   */
  public function __construct() {
    $url = $_SERVER['REQUEST_URI'];
    $request = parse_url($url);
    if (isset($request['fragment'])) {
      $this->fragment = $request['fragment'];
    }
    $path = urldecode($request['path']);
    if (App::getWebRoot() != '/') {
      $path = str_replace(App::getWebRoot(), '', $path);
    }
    $path = explode('/', $path);
    $this->path = array();
    foreach ($path as $dir) {
      if (!empty($dir)) {
        $this->path[] = $dir;
      }
    }

    $this->realPath = $this->path;

    $this->query = $_GET;
    $this->data = $_POST;

    $this->cookies = new Cookies($_COOKIE, SESSION_PREFIX);
    $this->session = new Session(SESSION_PREFIX);
  }

  /**
   * Get value of property
   * @param string $name Property name
   * @return mixed Value of property
   */
  public function __get($name) {
    switch ($name) {
      case 'path':
      case 'realPath':
      case 'data':
      case 'query':
      case 'cookies':
      case 'session':
      case 'fragment':
        return $this->$name;
      case 'ip':
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
      case 'url':
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
      case 'referer':
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }
  }

  /**
   * Set value of property
   * @param string $name Name of property
   * @param string $value Value of property
   */
  public function __set($name, $value) {
    switch ($name) {
      case 'path':
      case 'query':
      case 'fragment':
        $this->$name = $value;
    }
  }

  /**
   * Unset the entire GET query array or part of it
   * @param string $key A specific key to unset
   */
  public function unsetQuery($key = null) {
    if (isset($key)) {
      $this->query = array();
    }
    else {
      unset($this->query[$key]);
    }
  }

  /**
   * Get the current access token or generate a new one
   * @return string Access token
   */
  public function getToken() {
    if (!isset($this->session['access_token'])) {
      $this->session['access_token'] = sha1(mt_rand());
    }
    return $this->session['access_token'];
  }

  /**
   * Compare the session access token with the POST'ed access token
   * @return bool True if they match, false otherwise
   */
  public function checkToken() {
    if (!isset($this->data['access_token'])
        OR !isset($this->session['access_token'])) {
      return false;
    }
    return $this->session['access_token'] === $this->data['access_token'];
  }

  /**
   * Whether or not the current request method is GET
   * @return bool True if GET, false if not
   */
  public function isGet() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  /**
   * Whether or not the current request method is POST
   * @return bool True if POST, false if not
   */
  public function isPost() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  /**
   * Whether or not the current request was made with AJAX
   * @return bool True if it is, false otherwise
   */
  public function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
  }

}
