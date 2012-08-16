<?php

class Request {

  private $realPath;

  private $path;

  private $query;
  
  private $cookies;

  private $session;
  
  private $fragment = NULL;

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
      case 'form':
        return $this->getFormData($form);
      case 'ip':
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
      case 'url':
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL;
      case 'referer':
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;
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

  public function getFormData($form) {

  }
  
  public function unsetQuery($key = NULL) {
    if (isset($key)) {
      $this->query = array();
    }
    else {
      unset($this->query[$key]);
    }
  }

  public function getToken($form) {
    if ($this->checkToken($form)) {
      return $this->session[$form . '_token'];
    }
    $token = sha1(mt_rand());
    $this->session[$form . '_token'] = $token;
    return $token;
  }

  public function checkToken($form) {
    if (!isset($this->data[$form]) OR !isset($this->data[$form]['token'])
      OR !isset($this->session[$form . '_token'])) {
      return FALSE;
    }
    $result = $this->session[$form . '_token'] === $this->data[$form]['token'];
    return $result;
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
