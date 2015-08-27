<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\Utilities;
use Jivoo\InvalidPropertyException;
use Jivoo\AccessControl\Random;
use Jivoo\Core\Binary;
use Jivoo\Core\Log\Logger;

/**
 * A class representing an HTTP request.
 * @property string[] $path The path relative to the application root as an array.
 * @property array $query The GET query as an associative array.
 * @property string $fragment The fragment.
 * @property array|null $route Currently selected route, {@see Routing}.
 * @property RequestToken|null $requestToken Provider of request tokens, must be
 * set for {@see hasValidData} to work.
 * @property-read string[] $realPath The original $path.
 * @property-read array $data POST data as an associative array.
 * @property-read UploadedFile[] $files File upload data.
 * @property-read Cookies $cookies Cookie access object.
 * @property-read string|null $ip The remote address or null if not set.
 * @property-read string|null $url The request uri or null if not set.
 * @property-read string|null $referrer HTTP referer or null if not set.
 * @property-read string|null $referer HTTP referer or null if not set
 * (intentional misspelling).
 * @property-read string|null $userAgent HTTP user agent or null if not set.
 * @property-read string|null $domainName Domain name protocol and port.
 * @property-read string|null $method Request method, e.g. 'GET', 'POST' etc.
 * @proeprty-read bool $secure Whether or not HTTPS was used for this request.
 */
class Request {

  /**
   * @var string[] Original path.
   */
  private $realPath;

  /**
   * @var string[] Path as array.
   */
  private $path;

  /**
   * @var array GET query.
   */
  private $query;

  /**
   * @var Cookies Cookies object.
   */
  private $cookies;

  /**
   * @var array|null Route.
   */
  private $route = null;

  /**
   * @var string Fragment. 
   */
  private $fragment = null;

  /**
   * @var array POST data.
   */
  private $data;
  
  /**
   * @var bool Whether or not POST data is available.
   */
  private $hasData = false;
  
  /**
   * @var array File upload data.
   */
  private $files;
  
  /**
   * @var bool Whether or not request is from mobile browser.
   */
  private $mobile = null;
  
  /**
   * @var string Domain name, protocol and port.
   */
  private $domainName = '';
  
  /**
   * @var string Request method, e.g. 'GET', 'POST' etc.
   */
  private $method = 'GET';

  /**
   * @var string[] List of types accepted by the client (assumes that the client
   * wants HTML if no accept header is set).
   */
  private $accepts = array('html');

  /**
   * @var string[] List of encodings accepted by the client.
   */
  private $encodings = array();
  
  /**
   * @var bool Whether or not HTTPS was used.
   */
  private $secure = false;
  
  /**
   * @var RequestToken
   */
  private $requestToken = null;

  /**
   * Construct request.
   * @param string $cookiePrefix Cookie prefix to use for cookies.
   * @param string $basePath Base path of application.
   */
  public function __construct($cookiePrefix = '', $basePath = '/') {
    $url = $_SERVER['REQUEST_URI'];
       
    $request = parse_url($url);
    
    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') {
      $this->secure = true;
      $this->domainName = 'https://';
    }
    else {
      $this->domainName = 'http://';
    }
    $this->domainName .= $_SERVER['SERVER_NAME'];
    if ($_SERVER['SERVER_PORT'] != 80) {
      $this->domainName .= ':' . $_SERVER['SERVER_PORT']; 
    }
    if (isset($request['fragment'])) {
      $this->fragment = $request['fragment'];
    }
    $path = $request['path'];
    if ($basePath != '/') {
      $l = strlen($basePath);
      if (substr($path, 0, $l) == $basePath) {
        $path = substr($path, $l);
      }
    }
    $this->path = array();
    $path = substr($path, 1);
    if ($path != '') {
      $path = explode('/', $path);
      foreach ($path as $dir)
        $this->path[] = urldecode($dir);
    }

    $this->realPath = $this->path;

    $this->query = $_GET;
    $this->data = $_POST;
    $this->files = UploadedFile::convert($_FILES);
    
    $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
    if ($this->method != 'GET')
      $this->hasData = true;
    if ($this->method == 'POST' and isset($this->data['method'])) {
      $method = strtoupper($this->data['method']);
      switch ($method) {
        case 'PUT':
        case 'PATCH':
        case 'DELETE':
          $this->method = $method;
      }
    }

    if (isset($_SERVER['HTTP_ACCEPT'])) {
      $contentTypes = explode(',', $_SERVER['HTTP_ACCEPT']);
      $this->accepts = array();
      foreach ($contentTypes as $contentType) {
        $contentType = explode(';', $contentType);
        $this->accepts[] = trim(strtolower($contentType[0]));
      }
    }

    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
      $encodings = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
      foreach ($encodings as $encoding) {
        $this->encodings[] = trim(strtolower($encoding));
      }
    }

    $this->cookies = new Cookies($_COOKIE, $cookiePrefix, $basePath);
  }

  /**
   * Get value of property.
   * @param string $name Property name.
   * @return mixed Value of property.
   * @throws InvalidPropertyException If unknown property.
   */
  public function __get($name) {
    switch ($name) {
      case 'route':
      case 'path':
      case 'realPath':
      case 'data':
      case 'files':
      case 'query':
      case 'cookies':
      case 'fragment':
      case 'domainName':
      case 'method':
      case 'secure':
      case 'requestToken':
        return $this->$name;
      case 'ip':
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
      case 'url':
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
      case 'referrer':
      case 'referer':
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
      case 'userAgent':
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $name));
  }

  /**
   * Set value of property.
   * @param string $name Property name.
   * @param string $value Value of property.
   * @throws InvalidPropertyException If unknown property.
   */
  public function __set($name, $value) {
    switch ($name) {
      case 'route':
      case 'path':
      case 'query':
      case 'fragment':
      case 'requestToken':
        $this->$name = $value;
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $name));
  }

  /**
   * Convert request to associative array.
   * @return array Aassociative array.
   */
  public function toArray() {
    return array(
      'path' => $this->path,
      'route' => $this->route,
      'realPath' => $this->realPath,
      'data' => $this->data,
      'query' => $this->query,
      'fragment' => $this->fragment,
      'domainName' => $this->domainName,
      'method' => $this->method,
      'secure' => $this->secure,
      'ip' => $this->ip,
      'url' => $this->url,
      'referer' => $this->referer,
      'referrer' => $this->referer,
      'userAgent' => $this->userAgent
    );
  }
  
  /**
   * Unset the entire GET query array or part of it.
   * @param string $key A specific key to unset.
   */
  public function unsetQuery($key = null) {
    if (!isset($key)) {
      $this->query = array();
    }
    else {
      unset($this->query[$key]);
    }
  }
  
  /**
   * Whether or not the current request is POST and has a valid access token.
   * @param string|string[] $key Optional key or list of keys to test for
   * existence in POST-data.
   * @return boolean True if valid, false otherwise.
   */
  public function hasValidData($key = null) {
    if (!$this->hasData) {
      return false;
    }
    if (isset($key)) {
      if (is_array($key)) {
        foreach ($key as $k) {
          if (!isset($this->data[$k])) 
            return false;
        }
      }
      else if (!isset($this->data[$key])) { 
        return false;
      }
    }
    return $this->checkToken();
  }
  
  /**
   * Create HTML for a hidden form input containing the access token.
   * @return string HTML for hidden input.
   */
  public function createHiddenToken() {
    return '<input type="hidden" name="access_token" value="' . $this->getToken() . '" />';
  }

  /**
   * Get the current access token or generate a new one.
   * @return string Access token.
   */
  public function getToken() {
    if (!isset($this->requestToken)) {
      trigger_error(tr('Request token missing. Is the session module missing?'), E_USER_WARNING);
      return '';
    }
    return $this->requestToken->getToken();
  }

  /**
   * Compare the session access token with the POST'ed access token.
   * @return bool True if they match, false otherwise.
   */
  public function checkToken() {
    if (!isset($this->requestToken)) {
      trigger_error(tr('Request token missing. Is the session module missing?'), E_USER_WARNING);
      return false;
    }
    if (!isset($this->data['access_token'])) {
      return false;
    }
    return $this->requestToken->getToken() === $this->data['access_token'];
  }

  /**
   * Whether or not the current request method is GET.
   * @return bool True if GET, false if not.
   */
  public function isGet() {
    return $this->method == 'GET';
  }

  /**
   * Whether or not the current request method is POST.
   * @return bool True if POST, false if not.
   */
  public function isPost() {
    return $this->method == 'POST';
  }

  /**
   * Whether or not the current request method is PATCH.
   * @return bool True if PATCH, false if not.
   */
  public function isPatch() {
    return $this->method == 'PATCH';
  }

  /**
   * Whether or not the current request method is DELETE.
   * @return bool True if DELETE, false if not.
   */
  public function isDelete() {
    return $this->method == 'DELETE';
  }

  /**
   * Whether or not the current request method is PUT.
   * @return bool True if PUT, false if not.
   */
  public function isPut() {
    return $this->method == 'PUT';
  }

  /**
   * Whether or not the client accepts the specified type. If the type is
   * omitted then a list of acceptable types is returned.
   * @param string $type Type, can be a MIME type or a file extension known by
   * {@see Utilities::convertType()}.
   * @return bool|string[] True if client accepts provided type, false otherwise.
   * List of accepted MIME types if type parameter omitted.
   */
  public function accepts($type = null) {
    if (!isset($type))
      return $this->accepts;
    return in_array(Utilities::convertType($type), $this->accepts);
  }

  /**
   * Whether or not the client accepts the specified encoding. If the type is
   * omitted then a list of acceptable encodings is returned.
   * @param string $encoding Encoding.
   * @return bool|string[] True if client accepts provided encoding, false otherwise.
   * List of accepted encodings if type parameter omitted.
   */
  public function acceptsEncoding($encoding = null) {
    if (!isset($encoding))
      return $this->encodings;
    return in_array($encoding, $this->encodings);
  }

  /**
   * Whether or not the current request was made with AJAX.
   * @return bool True if it is, false otherwise.
   */
  public function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
  }
  
  /**
   * Whether or  not the current request was made by a mobile browser.
   * @return boolean True if a mobile browser was detected, false otherwise.
   */
  public function isMobile() {
    if (!isset($this->mobile)) {
      $agent = strtolower($this->userAgent);
      $this->mobile = false;
      if (isset($agent)) {
        if (strpos($agent, 'android') !== false
            or strpos($agent, 'iphone') !== false
            or strpos($agent, 'ipad') !== false
            or strpos($agent, 'mobile') !== false // e.g. IEMobile
            or strpos($agent, 'phone') !== false // e.g. Windows Phone OS
            or strpos($agent, 'opera mini') !== false
            or strpos($agent, 'maemo') !== false
            or strpos($agent, 'blackberry') !== false
            or strpos($agent, 'nokia') !== false
            or strpos($agent, 'sonyericsson') !== false
            or strpos($agent, 'opera mobi') !== false
            or strpos($agent, 'symbos') !== false
            or strpos($agent, 'symbianos') !== false
            or strpos($agent, 'j2me') !== false) {
          $this->mobile = true;
        }
      }
    }
    return $this->mobile;
  }

}
