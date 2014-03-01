<?php
/**
 * A HTTP response
 */
abstract class Response {
  private $status;
  private $type;

  public function __construct($status, $type) {
    $this->status = $status;
    $this->type = $type;
  }

  public function __get($property) {
    switch ($property) {
      case 'status':
      case 'type':
        return $this->$property;
    }
  }

  public abstract function render();
}

