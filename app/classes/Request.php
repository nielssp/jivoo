<?php

class Request {

  private $realPath;

  private $path;

  private $query;
  
  private $cookies;

  private $session;
  
  private $fragment = null;

  private $data;

  public function __construct() {
    $url = $_SERVER['REQUEST_URI'];
    $request = parse_url($url);
    if (isset($request['fragment'])) {
      $this->fragment = $request['fragment'];
    }
    $path = urldecode($request['path']);
    if (WEBPATH != '/') {
      $path = str_replace(WEBPATH, '', $path);
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
  
  public function __set($name, $value) {
    switch ($name) {
      case 'path':
      case 'query':
      case 'fragment':
        $this->$name = $value;
    }
  }

  public function unsetQuery($key = null) {
    if (isset($key)) {
      $this->query = array();
    }
    else {
      unset($this->query[$key]);
    }
  }

  public function getToken() {
    if (!isset($this->session['access_token'])) {
      $this->session['access_token'] = sha1(mt_rand());
    }
    return $this->session['access_token'];
  }

  public function checkToken() {
    if (!isset($this->data['access_token']) OR !isset($this->session['access_token'])) {
      return false;
    }
    return $this->session['access_token'] === $this->data['access_token'];
  }

  public function isGet() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  public function isPost() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  public function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
      AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
  }

}
