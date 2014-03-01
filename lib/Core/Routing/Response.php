<?php
/**
 * A HTTP response
 */
abstract class Response {
  private $status;
  private $type;
  private $cache = null;
  private $modified = null;
  private $maxAge = null;

  public function __construct($status, $type) {
    $this->status = $status;
    $this->type = $type;
  }

  public function __get($property) {
    switch ($property) {
      case 'status':
      case 'type':
      case 'cache':
      case 'modified':
      case 'maxAge':
        return $this->$property;
      case 'body':
        return $this->getBody();
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'modified':
      case 'maxAge':
        $this->$property = $value;
    }
  }

  public function __isset($property) {
    return isset($this->$property);
  }

  public abstract function getBody();

  public function cache($public = true, $expires = '+1 year') {
    if (!is_int($expires))
      $expires = strtotime($expires);
    $this->maxAge = $expires - time();
    $this->cache = 'public';
  }
}

