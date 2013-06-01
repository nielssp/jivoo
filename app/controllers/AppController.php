<?php

class AppController extends Controller {
  protected $e = null;
  
  public function init() {
    $this->e = new Dictionary();
  }

  public function addExtension($object) {
    $class = get_class($object);
    $this->e->$class = $object;
  }
}