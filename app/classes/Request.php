<?php

class Request {

  private $path;

  private $query;

  private $data;

  public function __construct() {
    $uri = $_SERVER['REQUEST_URI'];
    $request = parse_url($uri);
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
    
    $this->query = $_GET;
    $this->data = $_POST;
  }

  public function __get($name) {
    switch ($name) {
      case 'path':
      case 'data':
      case 'query':
        return $this->$name;
    }
  }
  
  public function __set($name, $value) {
    switch ($name) {
      case 'path':
      case 'query':
        $this->$name = $value;
    }
  }
  
  public function unsetQuery($key = NULL) {
    if (isset($key)) {
      $this->query = array();
    }
    else {
      unset($this->query[$key]);
    }
  }

  public function isGet() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  public function isPost() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

}
