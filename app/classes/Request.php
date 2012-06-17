<?php

class Request {

  private $path;

  private $query;

  private $data;

  public function __construct() {
    $request = $_SERVER['REQUEST_URI'];
    $request = parse_url($request);
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

    $query = $_GET;
    $data = $_POST;
  }

  public function __get($name) {
    switch ($name) {
      case 'path':
      case 'data':
      case 'query':
        return $this->$name;
    }
  }

  public function isGet() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  public function isPost() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

}
